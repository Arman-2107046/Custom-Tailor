import React, { useState, useEffect, useMemo, useCallback, useRef } from "react";
import { Loader2, Layers, Scissors, Palette, Menu, X, AlertTriangle } from "lucide-react";

const DEBUG_DIAGRAMS = false;

/* ------------------------------------------------------------------ */
/*  Smart Image Cache with Lazy Loading                                */
/* ------------------------------------------------------------------ */
class SmartImageCache {
    constructor() {
        this.memoryCache = new Map();
        this.loadingPromises = new Map();
        this.listeners = new Set();
    }

    subscribe(listener) {
        this.listeners.add(listener);
        return () => this.listeners.delete(listener);
    }

    notifyListeners(loadingCount) {
        this.listeners.forEach(listener => listener(loadingCount));
    }

    async preloadImage(url) {
        if (!url) return null;

        if (this.memoryCache.has(url)) {
            return this.memoryCache.get(url);
        }

        if (this.loadingPromises.has(url)) {
            return this.loadingPromises.get(url);
        }

        const loadPromise = new Promise((resolve) => {
            const img = new Image();
            img.onload = () => {
                this.memoryCache.set(url, img);
                this.loadingPromises.delete(url);
                this.notifyListeners(this.loadingPromises.size);
                resolve(img);
            };
            img.onerror = () => {
                console.warn(`Failed to load image: ${url}`);
                this.loadingPromises.delete(url);
                this.notifyListeners(this.loadingPromises.size);
                resolve(null);
            };
            img.src = url;
        });

        this.loadingPromises.set(url, loadPromise);
        this.notifyListeners(this.loadingPromises.size);
        return loadPromise;
    }

    async preloadBatch(urls, onProgress) {
        const uniqueUrls = [...new Set(urls.filter(Boolean))];
        const totalImages = uniqueUrls.length;
        let loadedCount = 0;

        const uncachedUrls = uniqueUrls.filter(url => !this.memoryCache.has(url));

        const batchSize = 6;
        for (let i = 0; i < uncachedUrls.length; i += batchSize) {
            const batch = uncachedUrls.slice(i, i + batchSize);
            await Promise.all(batch.map(url => this.preloadImage(url)));

            loadedCount += batch.length;
            const progress = (loadedCount / totalImages) * 100;
            if (onProgress) onProgress(progress, loadedCount, totalImages);
        }

        return uniqueUrls;
    }

    isImageCached(url) {
        return this.memoryCache.has(url);
    }

    getLoadingCount() {
        return this.loadingPromises.size;
    }
}

const smartImageCache = new SmartImageCache();

/* ------------------------------------------------------------------ */
/*  Loading indicator component                                        */
/* ------------------------------------------------------------------ */
const LoadingIndicator = ({ loadingCount }) => {
    if (loadingCount === 0) return null;

    return (
        <div className="absolute top-4 right-4 z-50 flex items-center gap-2 px-3 py-1.5 bg-white/90 backdrop-blur-sm rounded-full shadow-lg animate-in fade-in slide-in-from-top-2">
            <Loader2 className="w-3.5 h-3.5 text-gray-900 animate-spin" />
            <span className="text-xs font-medium text-gray-700">
                Loading {loadingCount} image{loadingCount > 1 ? 's' : ''}...
            </span>
        </div>
    );
};

/* ------------------------------------------------------------------ */
/*  Canvas layer with smooth transition                                */
/* ------------------------------------------------------------------ */
const CanvasLayer = ({ layer, previousLayer }) => {
    const [currentImage, setCurrentImage] = useState(null);
    const [isLoading, setIsLoading] = useState(true);
    const [isTransitioning, setIsTransitioning] = useState(false);

    useEffect(() => {
        let isMounted = true;

        const loadNewImage = async () => {
            if (!layer.image) return;

            setIsLoading(true);

            if (currentImage !== layer.image) {
                setIsTransitioning(true);

                const cachedImg = await smartImageCache.preloadImage(layer.image);

                if (isMounted && cachedImg) {
                    await new Promise(resolve => setTimeout(resolve, 50));

                    if (isMounted) {
                        setCurrentImage(cachedImg.src);
                        setIsLoading(false);
                        setIsTransitioning(false);
                    }
                }
            } else {
                setIsLoading(false);
                setIsTransitioning(false);
            }
        };

        loadNewImage();

        return () => {
            isMounted = false;
        };
    }, [layer.image]);

    return (
        <div
            key={layer.id}
            className={`absolute inset-0 flex items-center justify-center pointer-events-none layer-enter ${
                isLoading ? 'opacity-90' : 'opacity-100'
            }`}
            style={{
                zIndex: layer.layerIndex,
                transition: 'opacity 0.3s ease-in-out, transform 0.3s ease-in-out',
                transform: isTransitioning ? 'scale(0.98)' : 'scale(1)'
            }}
        >
            {currentImage && (
                <img
                    src={currentImage}
                    alt={layer.type}
                    className="object-contain max-w-full max-h-full"
                    onError={(e) => {
                        console.error(`Failed to load ${layer.type}:`, layer.image);
                        e.target.style.display = "none";
                    }}
                />
            )}
        </div>
    );
};

/* ------------------------------------------------------------------ */
/*  Enhanced Selection Hook with loading state                         */
/* ------------------------------------------------------------------ */
const useSelectionWithLoading = (initialValue = null) => {
    const [selected, setSelected] = useState(initialValue);
    const [pendingSelection, setPendingSelection] = useState(null);
    const [isLoading, setIsLoading] = useState(false);
    const previousSelectionRef = useRef(initialValue);

    const selectWithLoading = useCallback(async (newSelection, loadImageFn) => {
        if (!newSelection) {
            previousSelectionRef.current = selected;
            setSelected(newSelection);
            return;
        }

        const imageUrl = loadImageFn ? loadImageFn(newSelection) : newSelection.image;

        if (imageUrl && smartImageCache.isImageCached(imageUrl)) {
            previousSelectionRef.current = selected;
            setSelected(newSelection);
            return;
        }

        setIsLoading(true);
        setPendingSelection(newSelection);

        try {
            if (imageUrl) {
                await smartImageCache.preloadImage(imageUrl);
            }

            previousSelectionRef.current = selected;
            setSelected(newSelection);
            setPendingSelection(null);
            setIsLoading(false);
        } catch (error) {
            console.error('Failed to load selection:', error);
            setPendingSelection(null);
            setIsLoading(false);
        }
    }, [selected]);

    return {
        selected,
        pendingSelection,
        isLoading,
        previousSelection: previousSelectionRef.current,
        selectWithLoading,
        setSelected
    };
};

const SuitDesigner = () => {
    const [suitData, setSuitData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [activeLoads, setActiveLoads] = useState(0);

    const {
        selected: selectedFabric,
        pendingSelection: pendingFabric,
        isLoading: isFabricLoading,
        selectWithLoading: selectFabricWithLoading,
        setSelected: setSelectedFabric
    } = useSelectionWithLoading(null);

    const {
        selected: selectedBody,
        pendingSelection: pendingBody,
        isLoading: isBodyLoading,
        selectWithLoading: selectBodyWithLoading,
        setSelected: setSelectedBody
    } = useSelectionWithLoading(null);

    const {
        selected: selectedSleeve,
        pendingSelection: pendingSleeve,
        isLoading: isSleeveLoading,
        selectWithLoading: selectSleeveWithLoading,
        setSelected: setSelectedSleeve
    } = useSelectionWithLoading(null);

    const {
        selected: selectedLapelCategory,
        pendingSelection: pendingLapelCategory,
        isLoading: isLapelCategoryLoading,
        selectWithLoading: selectLapelCategoryWithLoading,
        setSelected: setSelectedLapelCategory
    } = useSelectionWithLoading(null);

    const {
        selected: selectedLapel,
        pendingSelection: pendingLapel,
        isLoading: isLapelLoading,
        selectWithLoading: selectLapelWithLoading,
        setSelected: setSelectedLapel
    } = useSelectionWithLoading(null);

    const {
        selected: selectedSidePocket,
        pendingSelection: pendingSidePocket,
        isLoading: isSidePocketLoading,
        selectWithLoading: selectSidePocketWithLoading,
        setSelected: setSelectedSidePocket
    } = useSelectionWithLoading(null);

    const {
        selected: selectedChestPocket,
        pendingSelection: pendingChestPocket,
        isLoading: isChestPocketLoading,
        selectWithLoading: selectChestPocketWithLoading,
        setSelected: setSelectedChestPocket
    } = useSelectionWithLoading(null);

    const {
        selected: selectedLining,
        pendingSelection: pendingLining,
        isLoading: isLiningLoading,
        selectWithLoading: selectLiningWithLoading,
        setSelected: setSelectedLining
    } = useSelectionWithLoading(null);

    const {
        selected: selectedButton,
        pendingSelection: pendingButton,
        isLoading: isButtonLoading,
        selectWithLoading: selectButtonWithLoading,
        setSelected: setSelectedButton
    } = useSelectionWithLoading(null);

    const [liningMode, setLiningMode] = useState("default");
    const [showLiningSidebar, setShowLiningSidebar] = useState(false);
    const [activeTab, setActiveTab] = useState("fabric");

    const preloadedBodiesRef = useRef(new Set());
    const preloadedLapelsRef = useRef(new Set());
    const preloadedFabricComponentsRef = useRef(new Set());

    useEffect(() => {
        const unsubscribe = smartImageCache.subscribe((loadingCount) => {
            setActiveLoads(loadingCount);
        });

        return () => {
            if (unsubscribe) unsubscribe();
        };
    }, []);

    useEffect(() => {
        fetchSuitData();
    }, []);

    const fetchSuitData = async () => {
        try {
            setLoading(true);
            const response = await fetch("/api/configurator");

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            setSuitData(data);

            if (data.success && data.data && data.data.length > 0) {
                const defaultFabric = data.data.find(f => f.is_default) || data.data[0];
                setSelectedFabric(defaultFabric);
                initializeFabricDefaults(defaultFabric);

                await loadDefaultView(defaultFabric);
            }

            setLoading(false);
        } catch (err) {
            console.error("Error fetching suit data:", err);
            setError(err.message);
            setLoading(false);
        }
    };

    const loadDefaultView = useCallback(async (fabric) => {
        const urls = new Set();

        if (fabric.image) urls.add(fabric.image);

        const defaultBody = fabric.bodies?.find(b => b.is_default) || fabric.bodies?.[0];
        if (defaultBody) {
            if (defaultBody.image) urls.add(defaultBody.image);
            if (defaultBody.body_type?.diagram) urls.add(defaultBody.body_type.diagram);

            if (defaultBody.default_linings?.[0]) {
                if (defaultBody.default_linings[0].image) urls.add(defaultBody.default_linings[0].image);
                if (defaultBody.default_linings[0].type?.diagram) urls.add(defaultBody.default_linings[0].type.diagram);
            }

            if (defaultBody.body_type?.body_buttons) {
                defaultBody.body_type.body_buttons.forEach(button => {
                    if (button.image) urls.add(button.image);
                    if (button.button_image?.diagram) urls.add(button.button_image.diagram);
                });
            }

            // Find default lapel
            const defaultLapel = defaultBody.lapels?.find((l) =>
                l.subcategory?.is_default === true || l.is_default === true
            ) || defaultBody.lapels?.find((l) =>
                l.category?.is_default === true
            ) || defaultBody.lapels?.[0];

            if (defaultLapel) {
                if (defaultLapel.image) urls.add(defaultLapel.image);
                if (defaultLapel.category?.diagram) urls.add(defaultLapel.category.diagram);
                if (defaultLapel.subcategory?.diagram) urls.add(defaultLapel.subcategory.diagram);
            }
        }

        const defaultSleeve = fabric.sleeves?.find(s => s.is_default) || fabric.sleeves?.[0];
        if (defaultSleeve) {
            if (defaultSleeve.image) urls.add(defaultSleeve.image);
            if (defaultSleeve.type?.diagram) urls.add(defaultSleeve.type.diagram);
        }

        const defaultSidePocket = fabric.side_pockets?.find(p => p.is_default) || fabric.side_pockets?.[0];
        if (defaultSidePocket) {
            if (defaultSidePocket.image) urls.add(defaultSidePocket.image);
            if (defaultSidePocket.type?.diagram) urls.add(defaultSidePocket.type.diagram);
        }

        const defaultChestPocket = fabric.chest_pockets?.find(p => p.is_default) || fabric.chest_pockets?.[0];
        if (defaultChestPocket) {
            if (defaultChestPocket.image) urls.add(defaultChestPocket.image);
            if (defaultChestPocket.type?.diagram) urls.add(defaultChestPocket.type.diagram);
        }

        const urlArray = [...urls];
        console.log(`Loading default view: ${urlArray.length} images`);
        await smartImageCache.preloadBatch(urlArray);
    }, []);

    const loadAllBodies = useCallback(async (fabric) => {
        if (!fabric || preloadedBodiesRef.current.has(fabric.id)) return;

        const urls = new Set();
        for (const body of (fabric.bodies || [])) {
            if (body.image) urls.add(body.image);
            if (body.body_type?.diagram) urls.add(body.body_type.diagram);
            if (body.default_linings?.[0]?.image) urls.add(body.default_linings[0].image);
            if (body.default_linings?.[0]?.type?.diagram) urls.add(body.default_linings[0].type.diagram);
        }

        const urlArray = [...urls];
        console.log(`Loading ${urlArray.length} body images for ${fabric.name}`);
        await smartImageCache.preloadBatch(urlArray);

        preloadedBodiesRef.current.add(fabric.id);
    }, []);

    const loadLapelsForBody = useCallback(async (body) => {
        if (!body || preloadedLapelsRef.current.has(body.id)) return;

        const urls = new Set();
        for (const lapel of (body.lapels || [])) {
            if (lapel.image) urls.add(lapel.image);
            if (lapel.category?.diagram) urls.add(lapel.category.diagram);
            if (lapel.subcategory?.diagram) urls.add(lapel.subcategory.diagram);
        }

        const urlArray = [...urls];
        console.log(`Loading ${urlArray.length} lapel images for body ${body.id}`);
        await smartImageCache.preloadBatch(urlArray);

        preloadedLapelsRef.current.add(body.id);
    }, []);

    const loadRemainingComponents = useCallback(async (fabric) => {
        if (!fabric || preloadedFabricComponentsRef.current.has(fabric.id)) return;

        const urls = new Set();

        for (const sleeve of (fabric.sleeves || [])) {
            if (sleeve.image) urls.add(sleeve.image);
            if (sleeve.type?.diagram) urls.add(sleeve.type.diagram);
        }

        for (const pocket of (fabric.side_pockets || [])) {
            if (pocket.image) urls.add(pocket.image);
            if (pocket.type?.diagram) urls.add(pocket.type.diagram);
        }

        for (const pocket of (fabric.chest_pockets || [])) {
            if (pocket.image) urls.add(pocket.image);
            if (pocket.type?.diagram) urls.add(pocket.type.diagram);
        }

        for (const lining of (fabric.custom_linings || [])) {
            if (lining.image) urls.add(lining.image);
            if (lining.fabric?.image) urls.add(lining.fabric.image);
            if (lining.type?.diagram) urls.add(lining.type.diagram);
        }

        const urlArray = [...urls];
        console.log(`Loading ${urlArray.length} remaining component images`);
        await smartImageCache.preloadBatch(urlArray);

        preloadedFabricComponentsRef.current.add(fabric.id);
    }, []);

    const handleFabricChange = async (fabric) => {
        setSelectedFabric(fabric);
        initializeFabricDefaults(fabric);

        await loadDefaultView(fabric);

        loadAllBodies(fabric);
    };

    const handleBodyChange = async (body) => {
        await selectBodyWithLoading(body, (b) => b.image || b.body_type?.diagram);

        // Reset button to default (null) when body changes
        setSelectedButton(null);

        if (body.lapels && body.lapels.length > 0) {
            // Find default lapel by checking subcategory.is_default or category.is_default
            const defaultLapel = body.lapels.find((l) =>
                l.subcategory?.is_default === true || l.is_default === true
            ) || body.lapels.find((l) =>
                l.category?.is_default === true
            ) || body.lapels[0];

            await selectLapelWithLoading(defaultLapel, (l) => l.image);
            await selectLapelCategoryWithLoading(defaultLapel.category, (c) => c?.diagram);

            loadLapelsForBody(body);
        } else {
            setSelectedLapelCategory(null);
            setSelectedLapel(null);
        }
    };

    const handleLapelCategoryChange = async (category) => {
        await selectLapelCategoryWithLoading(category, (c) => c?.diagram);

        const categoryLapels = selectedBody?.lapels?.filter((l) => l.category?.id === category.id);
        if (categoryLapels && categoryLapels.length > 0) {
            // Find default subcategory by checking subcategory.is_default
            const defaultSub = categoryLapels.find((l) =>
                l.subcategory?.is_default === true || l.is_default === true
            ) || categoryLapels[0];

            await selectLapelWithLoading(defaultSub, (l) => l.subcategory?.diagram || l.image);
        }
    };

    const handleLapelSelect = async (lapel) => {
        await selectLapelWithLoading(lapel, (l) => l.subcategory?.diagram || l.image);
    };

    const handleSleeveSelect = async (sleeve) => {
        await selectSleeveWithLoading(sleeve, (s) => s.type?.diagram || s.image);
    };

    const handleSidePocketSelect = async (pocket) => {
        await selectSidePocketWithLoading(pocket, (p) => p.type?.diagram || p.image);
    };

    const handleChestPocketSelect = async (pocket) => {
        await selectChestPocketWithLoading(pocket, (p) => p.type?.diagram || p.image);
    };

    const handleButtonSelect = async (button) => {
        await selectButtonWithLoading(button, (b) => b.button_image?.diagram || b.image);
    };

    const handleDefaultButtonClick = () => {
        setSelectedButton(null);
    };

    const handleTabChange = (tabId) => {
        setActiveTab(tabId);

        if (tabId === "style" && selectedFabric) {
            loadAllBodies(selectedFabric);
            loadRemainingComponents(selectedFabric);
        } else if (tabId === "accents" && selectedFabric) {
            loadRemainingComponents(selectedFabric);
        }
    };

    const getLapelCategories = (lapels) => {
        if (!lapels || lapels.length === 0) return [];
        const categories = {};
        lapels.forEach((lapel) => {
            const categoryId = lapel.category?.id;
            if (categoryId && !categories[categoryId]) {
                categories[categoryId] = {
                    id: categoryId,
                    name: lapel.category?.name,
                    diagram: lapel.category?.diagram,
                    is_default: lapel.category?.is_default || false,
                    subcategories: [],
                };
            }
            if (categoryId) categories[categoryId].subcategories.push(lapel);
        });
        return Object.values(categories);
    };

    const initializeFabricDefaults = useCallback((fabric) => {
        setSelectedBody(null);
        setSelectedSleeve(null);
        setSelectedLapelCategory(null);
        setSelectedLapel(null);
        setSelectedSidePocket(null);
        setSelectedChestPocket(null);
        setSelectedLining(null);
        setSelectedButton(null);
        setLiningMode("default");
        setShowLiningSidebar(false);

        if (fabric.bodies && fabric.bodies.length > 0) {
            const defaultBody = fabric.bodies.find((b) => b.is_default) || fabric.bodies[0];
            setSelectedBody(defaultBody);

            if (defaultBody.lapels && defaultBody.lapels.length > 0) {
                // Find default lapel by checking subcategory.is_default or category.is_default
                const defaultLapel = defaultBody.lapels.find((l) =>
                    l.subcategory?.is_default === true || l.is_default === true
                ) || defaultBody.lapels.find((l) =>
                    l.category?.is_default === true
                ) || defaultBody.lapels[0];

                setSelectedLapel(defaultLapel);
                setSelectedLapelCategory(defaultLapel.category);
            }
        }

        if (fabric.sleeves && fabric.sleeves.length > 0) {
            const defaultSleeve = fabric.sleeves.find((s) => s.is_default) || fabric.sleeves[0];
            setSelectedSleeve(defaultSleeve);
        }

        if (fabric.side_pockets && fabric.side_pockets.length > 0) {
            const defaultPocket = fabric.side_pockets.find((p) => p.is_default) || fabric.side_pockets[0];
            setSelectedSidePocket(defaultPocket);
        }

        if (fabric.chest_pockets && fabric.chest_pockets.length > 0) {
            const defaultPocket = fabric.chest_pockets.find((p) => p.is_default) || fabric.chest_pockets[0];
            setSelectedChestPocket(defaultPocket);
        }
    }, []);

    const handleDefaultLiningClick = () => {
        setLiningMode("default");
        setSelectedLining(null);
        setShowLiningSidebar(false);
    };

    const handleCustomLiningClick = () => {
        setLiningMode("custom");
        setShowLiningSidebar(true);

        if (selectedFabric) {
            const urls = new Set();
            (selectedFabric.custom_linings || []).forEach(lining => {
                if (lining.image) urls.add(lining.image);
                if (lining.fabric?.image) urls.add(lining.fabric.image);
                if (lining.type?.diagram) urls.add(lining.type.diagram);
            });
            smartImageCache.preloadBatch([...urls]);
        }
    };

    const handleLiningSelect = async (lining) => {
        await selectLiningWithLoading(lining, (l) => l.fabric?.image || l.image);
        setLiningMode("custom");
        setShowLiningSidebar(false);
    };

    const layers = useMemo(() => {
        const allLayers = [];

        const effectiveLining = pendingLining || selectedLining;
        const effectiveBody = pendingBody || selectedBody;
        const effectiveSleeve = pendingSleeve || selectedSleeve;
        const effectiveLapel = pendingLapel || selectedLapel;
        const effectiveSidePocket = pendingSidePocket || selectedSidePocket;
        const effectiveChestPocket = pendingChestPocket || selectedChestPocket;
        const effectiveButton = pendingButton || selectedButton;

        if (effectiveBody?.default_linings && effectiveBody.default_linings.length > 0) {
            const defaultLining = effectiveBody.default_linings[0];
            allLayers.push({
                id: `default-lining-${defaultLining.id}`,
                type: "defaultLining",
                image: defaultLining.image,
                layerIndex: defaultLining.layer_index || 0,
            });
        }

        if (effectiveLining) {
            allLayers.push({
                id: `lining-${effectiveLining.id}`,
                type: "lining",
                image: effectiveLining.image,
                layerIndex: effectiveLining.layer_index || 100,
            });
        }

        if (effectiveBody) {
            allLayers.push({
                id: `body-${effectiveBody.id}`,
                type: "body",
                image: effectiveBody.image,
                layerIndex: effectiveBody.layer_index || 100,
            });
        }

        if (effectiveSleeve) {
            allLayers.push({
                id: `sleeve-${effectiveSleeve.id}`,
                type: "sleeve",
                image: effectiveSleeve.image,
                layerIndex: effectiveSleeve.layer_index || 150,
            });
        }

        if (effectiveLapel) {
            allLayers.push({
                id: `lapel-${effectiveLapel.id}`,
                type: "lapel",
                image: effectiveLapel.image,
                layerIndex: effectiveLapel.layer_index || 150,
            });
        }

        if (effectiveSidePocket) {
            allLayers.push({
                id: `sidePocket-${effectiveSidePocket.id}`,
                type: "sidePocket",
                image: effectiveSidePocket.image,
                layerIndex: effectiveSidePocket.layer_index || 100,
            });
        }

        if (effectiveChestPocket) {
            allLayers.push({
                id: `chestPocket-${effectiveChestPocket.id}`,
                type: "chestPocket",
                image: effectiveChestPocket.image,
                layerIndex: effectiveChestPocket.layer_index || 100,
            });
        }

        if (effectiveButton) {
            allLayers.push({
                id: `button-${effectiveButton.id}`,
                type: "button",
                image: effectiveButton.image,
                layerIndex: effectiveButton.layer_index || 160,
            });
        }

        return allLayers.sort((a, b) => a.layerIndex - b.layerIndex);
    }, [
        pendingLining, selectedLining,
        pendingBody, selectedBody,
        pendingSleeve, selectedSleeve,
        pendingLapel, selectedLapel,
        pendingSidePocket, selectedSidePocket,
        pendingChestPocket, selectedChestPocket,
        pendingButton, selectedButton
    ]);

    const FabricOptionTile = ({ isSelected, onClick, image, label, price, isLoading }) => {
        const [cachedImage, setCachedImage] = useState(null);
        const [failed, setFailed] = useState(false);

        useEffect(() => {
            if (image) {
                smartImageCache.preloadImage(image).then(img => {
                    if (img) setCachedImage(img.src);
                });
            }
        }, [image]);

        return (
            <button
                onClick={onClick}
                className={`relative p-3 rounded-lg transition-all duration-300 ease-out ${
                    isSelected
                        ? " shadow-md scale-[1.02]"
                        : " hover:shadow-md hover:scale-[1.02]"
                }`}
            >
                {isSelected && (
                    <div className="absolute z-20 flex items-center justify-center w-5 h-5 text-xs text-white duration-200 bg-gray-900 rounded-full top-2 right-2 animate-in fade-in zoom-in">
                        ✓
                    </div>
                )}
                {isLoading && (
                    <div className="absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-white/50">
                        <Loader2 className="w-5 h-5 text-gray-900 animate-spin" />
                    </div>
                )}
                <div className="w-full aspect-[4/3] flex items-center justify-center bg-transparent rounded-md overflow-hidden">
                    {(cachedImage || image) && !failed ? (
                        <img
                            src={cachedImage || image}
                            alt={label}
                            className="object-contain w-full h-full"
                            onError={() => setFailed(true)}
                        />
                    ) : (
                        <div className="flex items-center justify-center w-full h-full text-xs text-gray-400">
                            {label || "No image"}
                        </div>
                    )}
                </div>
                <div className="mt-2">
                    <div className="text-xs font-medium leading-tight text-center text-gray-700">{label}</div>
                    {price && <div className="text-xs text-center text-gray-500 mt-0.5">${price}</div>}
                </div>
            </button>
        );
    };

    const StyleOptionTile = ({ isSelected, onClick, image, hadDiagramField, label, aspect = "aspect-[3/4]", isLoading }) => {
        const [cachedImage, setCachedImage] = useState(null);
        const [failed, setFailed] = useState(false);
        const showImage = Boolean(cachedImage || image) && !failed;

        useEffect(() => {
            if (image) {
                smartImageCache.preloadImage(image).then(img => {
                    if (img) setCachedImage(img.src);
                });
            }
        }, [image]);

        return (
            <button
                onClick={onClick}
                className={`relative p-3 rounded-lg transition-all duration-300 ease-out ${
                    isSelected
                        ? " rounded-lg bg-transparent scale-[1.03]"
                        : "rounded-lg hover:scale-[1.03]"
                }`}
                title={DEBUG_DIAGRAMS ? image || "no image URL resolved" : undefined}
            >
                {isSelected && (
                    <div className="absolute z-20 flex items-center justify-center w-5 h-5 text-xs text-white duration-200 bg-gray-900 rounded-full top-2 right-2 animate-in fade-in zoom-in">
                        ✓
                    </div>
                )}
                {isLoading && (
                    <div className="absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-white/50">
                        <Loader2 className="w-5 h-5 text-gray-900 animate-spin" />
                    </div>
                )}

                {DEBUG_DIAGRAMS && !hadDiagramField && (
                    <div
                        className="absolute z-20 w-2.5 h-2.5 rounded-full bg-yellow-400 top-2 left-2"
                        title="No `diagram` field on this record"
                    />
                )}
                {DEBUG_DIAGRAMS && hadDiagramField && failed && (
                    <div
                        className="absolute z-20 flex items-center justify-center w-4 h-4 bg-red-500 rounded-full top-2 left-2"
                        title="diagram field present but image failed"
                    >
                        <AlertTriangle className="w-2.5 h-2.5 text-white" />
                    </div>
                )}

                <div className={`w-full ${aspect} flex items-center justify-center rounded-md overflow-hidden bg-white`}>
                    {showImage ? (
                        <img
                            src={cachedImage || image}
                            alt={label}
                            className="object-contain w-full h-full p-2"
                            style={{ mixBlendMode: "multiply" }}
                            onError={() => {
                                if (DEBUG_DIAGRAMS) console.warn(`Failed to load image for "${label}":`, image);
                                setFailed(true);
                            }}
                        />
                    ) : (
                        <div className="flex items-center justify-center w-full h-full p-2 text-xs text-center text-gray-400">
                            <Loader2 className="w-4 h-4 animate-spin" />
                        </div>
                    )}
                </div>
                <div className="mt-2">
                    <div className="text-xs font-medium leading-tight text-center text-gray-700">{label}</div>
                </div>
            </button>
        );
    };

    const LiningOptionTile = ({ isSelected, onClick, image, label, isLoading }) => {
        const [cachedImage, setCachedImage] = useState(null);
        const [failed, setFailed] = useState(false);

        useEffect(() => {
            if (image) {
                smartImageCache.preloadImage(image).then(img => {
                    if (img) setCachedImage(img.src);
                });
            }
        }, [image]);

        return (
            <button
                onClick={onClick}
                className={`relative p-3 rounded-lg transition-all duration-300 ease-out ${
                    isSelected
                        ? " scale-[1.02]"
                        : " hover:scale-[1.02]"
                }`}
            >
                {isSelected && (
                    <div className="absolute z-20 flex items-center justify-center w-5 h-5 text-xs text-white duration-200 bg-gray-900 rounded-full top-2 right-2">
                        ✓
                    </div>
                )}
                {isLoading && (
                    <div className="absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-white/50">
                        <Loader2 className="w-5 h-5 text-gray-900 animate-spin" />
                    </div>
                )}
                <div className="w-full aspect-[4/3] flex items-center justify-center bg-transparent rounded-md overflow-hidden">
                    {(cachedImage || image) && !failed ? (
                        <img
                            src={cachedImage || image}
                            alt={label}
                            className="object-contain w-full h-full p-2"
                            style={{ mixBlendMode: "multiply" }}
                            onError={() => setFailed(true)}
                        />
                    ) : (
                        <div className="flex items-center justify-center w-full h-full text-xs text-gray-400">
                            <Loader2 className="w-4 h-4 animate-spin" />
                        </div>
                    )}
                </div>
                {/* <div className="mt-2">
                    <div className="text-xs font-medium leading-tight text-center text-gray-700">{label}</div>
                </div> */}
            </button>
        );
    };

    const ButtonOptionTile = ({ isSelected, onClick, image, label, isLoading, isDefault = false }) => {
        const [cachedImage, setCachedImage] = useState(null);
        const [failed, setFailed] = useState(false);

        useEffect(() => {
            if (image) {
                smartImageCache.preloadImage(image).then(img => {
                    if (img) setCachedImage(img.src);
                });
            }
        }, [image]);

        return (
            <button
                onClick={onClick}
                className={`relative p-3 rounded-lg transition-all duration-300 ease-out ${
                    isSelected
                        ? " scale-[1.02]"
                        : " hover:scale-[1.02]"
                }`}
            >
                {isSelected && (
                    <div className="absolute z-20 flex items-center justify-center w-5 h-5 text-xs text-white duration-200 bg-gray-900 rounded-full top-2 right-2">
                        ✓
                    </div>
                )}
                {isLoading && (
                    <div className="absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-white/50">
                        <Loader2 className="w-5 h-5 text-gray-900 animate-spin" />
                    </div>
                )}
                <div className="flex items-center justify-center w-full overflow-hidden rounded-md aspect-square transparent">
                    {isDefault ? (
                        <div className="flex flex-col items-center justify-center w-full h-full p-2">
                            <div className="flex items-center justify-center w-12 h-12 mb-1 border-4 border-gray-900 rounded-full">
                                <span className="text-xs text-gray-400">−</span>
                            </div>
                            {/* <span className="text-[10px] text-gray-400">No Button</span> */}
                        </div>
                    ) : (cachedImage || image) && !failed ? (
                        <img
                            src={cachedImage || image}
                            alt={label}
                            className="object-contain w-full h-full p-2"
                            style={{ mixBlendMode: "multiply" }}
                            onError={() => setFailed(true)}
                        />
                    ) : (
                        <div className="flex items-center justify-center w-full h-full text-xs text-gray-400">
                            <Loader2 className="w-4 h-4 animate-spin" />
                        </div>
                    )}
                </div>
                <div className="mt-2">
                    <div className="text-xs font-medium leading-tight text-center text-gray-700">{label}</div>
                </div>
            </button>
        );
    };

    const SectionHeader = ({ title }) => (
        <div className="px-5 pt-6 pb-3">
            <span className="text-xs font-bold tracking-wider text-gray-400 uppercase">{title}</span>
        </div>
    );

    const RailTab = ({ id, icon: Icon, label }) => {
        const isActive = activeTab === id;
        return (
            <button
                type="button"
                onClick={() => handleTabChange(id)}
                className="flex flex-col items-center gap-1.5 py-3 group w-full transition-transform duration-200 hover:scale-105"
            >
                <div
                    className={`flex items-center justify-center w-11 h-11 rounded-full border-2 transition-all duration-300 ${
                        isActive
                            ? "bg-gray-900 border-gray-900 text-white shadow-md scale-110"
                            : "bg-white border-gray-200 text-gray-400 group-hover:border-gray-400 group-hover:text-gray-600"
                    }`}
                >
                    <Icon className="w-5 h-5" />
                </div>
                <span
                    className={`text-[11px] font-semibold tracking-wide uppercase transition-colors duration-300 ${
                        isActive ? "text-gray-900" : "text-gray-400"
                    }`}
                >
                    {label}
                </span>
            </button>
        );
    };

    if (loading) {
        return (
            <div className="flex items-center justify-center h-screen bg-gray-50">
                <div className="text-center duration-500 animate-in fade-in zoom-in">
                    <Loader2 className="w-12 h-12 mx-auto mb-4 text-gray-900 animate-spin" />
                    <p className="text-lg text-gray-600">Loading customization options...</p>
                </div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="flex items-center justify-center h-screen bg-gray-50">
                <div className="max-w-md p-8 text-center duration-500 bg-white shadow-lg rounded-xl animate-in fade-in slide-in-from-bottom-4">
                    <div className="mb-4 text-5xl">⚠️</div>
                    <h2 className="mb-2 text-2xl font-bold text-gray-800">Error Loading Data</h2>
                    <p className="mb-4 text-gray-600">{error}</p>
                    <button
                        onClick={fetchSuitData}
                        className="px-6 py-2 text-white transition-all duration-200 bg-gray-900 rounded-lg hover:bg-gray-800 hover:shadow-md active:scale-95"
                    >
                        Retry
                    </button>
                </div>
            </div>
        );
    }

    if (!suitData || !suitData.success || !selectedFabric) {
        return (
            <div className="flex items-center justify-center h-screen bg-gray-50">
                <p className="text-gray-600">No data available</p>
            </div>
        );
    }

    const lapelCategories = selectedBody ? getLapelCategories(selectedBody.lapels) : [];
    const filteredLapels =
        selectedLapelCategory && selectedBody
            ? selectedBody.lapels.filter((l) => l.category?.id === selectedLapelCategory.id)
            : [];

    return (
        <div className="flex h-screen bg-white">
            <style>{`
                @keyframes layerEnter {
                    from {
                        opacity: 0;
                        transform: scale(0.96) translateY(6px);
                        filter: blur(2px);
                    }
                    to {
                        opacity: 1;
                        transform: scale(1) translateY(0);
                        filter: blur(0);
                    }
                }
                .layer-enter {
                    animation: layerEnter 0.45s cubic-bezier(0.22, 1, 0.36, 1) forwards;
                }
                @keyframes layerTransition {
                    from {
                        opacity: 0.9;
                        transform: scale(0.98);
                    }
                    to {
                        opacity: 1;
                        transform: scale(1);
                    }
                }
                .layer-transition {
                    animation: layerTransition 0.3s ease-out forwards;
                }
            `}</style>

            {/* SIDEBAR */}
            <div className="z-10 flex shadow-lg shrink-0">
                {/* Content Panel */}
                <div className="overflow-y-auto bg-white border-r border-gray-100 w-96">
                    <div className="px-5 py-5 border-b border-gray-100">
                        <h1 className="text-xl font-semibold text-gray-900 transition-all duration-300">
                            {activeTab === "fabric" && "Choose your fabric"}
                            {activeTab === "style" && "Customize your style"}
                            {activeTab === "accents" && "Accents & lining"}
                        </h1>
                        <p className="mt-1 text-sm text-gray-500">
                            {activeTab === "fabric" && "Select the material for your suit"}
                            {activeTab === "style" && "Personalize the cut and details"}
                            {activeTab === "accents" && "Add the finishing touches"}
                        </p>
                    </div>

                    {/* FABRIC TAB */}
                    {activeTab === "fabric" && (
                        <div className="p-5 duration-300 animate-in fade-in slide-in-from-left-2">
                            <div className="grid grid-cols-2 gap-3">
                                {suitData.data.map((fabric) => (
                                    <FabricOptionTile
                                        key={fabric.id}
                                        isSelected={fabric.id === selectedFabric?.id}
                                        onClick={() => handleFabricChange(fabric)}
                                        image={fabric.image}
                                        label={fabric.name}
                                        price={fabric.price}
                                        isLoading={isFabricLoading && pendingFabric?.id === fabric.id}
                                    />
                                ))}
                            </div>
                        </div>
                    )}

                    {/* STYLE TAB */}
                    {activeTab === "style" && (
                        <div className="pb-5 duration-300 animate-in fade-in slide-in-from-left-2">
                            {/* Body Styles */}
                            {selectedFabric?.bodies && selectedFabric.bodies.length > 0 && (
                                <>
                                    <SectionHeader title="Body Style" />
                                    <div className="grid grid-cols-3 gap-3 px-5">
                                        {selectedFabric.bodies.map((body) => (
                                            <StyleOptionTile
                                                key={body.id}
                                                isSelected={body.id === selectedBody?.id}
                                                onClick={() => handleBodyChange(body)}
                                                image={body.body_type?.diagram || body.image}
                                                hadDiagramField={Boolean(body.body_type?.diagram)}
                                                label={body.body_type?.name || "Body"}
                                                isLoading={isBodyLoading && pendingBody?.id === body.id}
                                            />
                                        ))}
                                    </div>
                                </>
                            )}

                            {/* Lapels */}
                            {selectedBody?.lapels && selectedBody.lapels.length > 0 && (
                                <>
                                    <SectionHeader title="Lapel Type" />
                                    <div className="grid grid-cols-3 gap-3 px-5">
                                        {lapelCategories.map((category) => (
                                            <StyleOptionTile
                                                key={category.id}
                                                isSelected={selectedLapelCategory?.id === category.id}
                                                onClick={() => handleLapelCategoryChange(category)}
                                                image={category.diagram}
                                                hadDiagramField={Boolean(category.diagram)}
                                                label={category.name}
                                                isLoading={isLapelCategoryLoading && pendingLapelCategory?.id === category.id}
                                            />
                                        ))}
                                    </div>

                                    {selectedLapelCategory && filteredLapels.length > 0 && (
                                        <>
                                            <SectionHeader title={`${selectedLapelCategory.name} Width`} />
                                            <div className="grid grid-cols-3 gap-3 px-5">
                                                {filteredLapels.map((lapel) => (
                                                    <StyleOptionTile
                                                        key={lapel.id}
                                                        isSelected={lapel.id === selectedLapel?.id}
                                                        onClick={() => handleLapelSelect(lapel)}
                                                        image={lapel.subcategory?.diagram || lapel.image}
                                                        hadDiagramField={Boolean(lapel.subcategory?.diagram)}
                                                        label={lapel.subcategory?.name || "Width"}
                                                        isLoading={isLapelLoading && pendingLapel?.id === lapel.id}
                                                    />
                                                ))}
                                            </div>
                                        </>
                                    )}
                                </>
                            )}

                            {/* Sleeves */}
                            {selectedFabric?.sleeves && selectedFabric.sleeves.length > 0 && (
                                <>
                                    <SectionHeader title="Sleeves" />
                                    <div className="grid grid-cols-3 gap-3 px-5">
                                        {selectedFabric.sleeves.map((sleeve) => (
                                            <StyleOptionTile
                                                key={sleeve.id}
                                                isSelected={sleeve.id === selectedSleeve?.id}
                                                onClick={() => handleSleeveSelect(sleeve)}
                                                image={sleeve.type?.diagram || sleeve.image}
                                                hadDiagramField={Boolean(sleeve.type?.diagram)}
                                                label={sleeve.type?.name || "Sleeve"}
                                                isLoading={isSleeveLoading && pendingSleeve?.id === sleeve.id}
                                            />
                                        ))}
                                    </div>
                                </>
                            )}

                            {/* Side Pockets */}
                            {selectedFabric?.side_pockets && selectedFabric.side_pockets.length > 0 && (
                                <>
                                    <SectionHeader title="Side Pockets" />
                                    <div className="grid grid-cols-3 gap-3 px-5">
                                        {selectedFabric.side_pockets.map((pocket) => (
                                            <StyleOptionTile
                                                key={pocket.id}
                                                isSelected={pocket.id === selectedSidePocket?.id}
                                                onClick={() => handleSidePocketSelect(pocket)}
                                                image={pocket.type?.diagram || pocket.image}
                                                hadDiagramField={Boolean(pocket.type?.diagram)}
                                                label={pocket.type?.name || "Pocket"}
                                                isLoading={isSidePocketLoading && pendingSidePocket?.id === pocket.id}
                                            />
                                        ))}
                                    </div>
                                </>
                            )}

                            {/* Chest Pockets */}
                            {selectedFabric?.chest_pockets && selectedFabric.chest_pockets.length > 0 && (
                                <>
                                    <SectionHeader title="Chest Pockets" />
                                    <div className="grid grid-cols-3 gap-3 px-5">
                                        {selectedFabric.chest_pockets.map((pocket) => (
                                            <StyleOptionTile
                                                key={pocket.id}
                                                isSelected={pocket.id === selectedChestPocket?.id}
                                                onClick={() => handleChestPocketSelect(pocket)}
                                                image={pocket.type?.diagram || pocket.image}
                                                hadDiagramField={Boolean(pocket.type?.diagram)}
                                                label={pocket.type?.name || "Pocket"}
                                                isLoading={isChestPocketLoading && pendingChestPocket?.id === pocket.id}
                                            />
                                        ))}
                                    </div>
                                </>
                            )}
                        </div>
                    )}

                    {/* ACCENTS TAB */}
                    {activeTab === "accents" && (
                        <div className="p-5 duration-300 animate-in fade-in slide-in-from-left-2">
                            {/* LINING TYPE SECTION */}
                            <div className="mb-6">
                                <h3 className="mb-4 text-sm font-semibold tracking-wide text-gray-900 uppercase">
                                    Lining Type
                                </h3>

                                <div className="grid grid-cols-2 gap-3">
                                    {selectedBody?.default_linings?.map((defaultLining) => (
                                        <LiningOptionTile
                                            key={`default-${defaultLining.id}`}
                                            isSelected={liningMode === "default" && !selectedLining}
                                            onClick={() => handleDefaultLiningClick()}
                                            image={defaultLining.type?.diagram || defaultLining.image}
                                            label={defaultLining.type?.name || "Default"}
                                            isLoading={false}
                                        />
                                    ))}

                                    {selectedFabric?.custom_linings && selectedFabric.custom_linings.length > 0 && (
                                        <LiningOptionTile
                                            key="custom-type"
                                            isSelected={liningMode === "custom"}
                                            onClick={handleCustomLiningClick}
                                            image={selectedFabric.custom_linings[0].type?.diagram}
                                            label={selectedFabric.custom_linings[0].type?.name || "Custom"}
                                            isLoading={false}
                                        />
                                    )}
                                </div>

                                {/* <div className="p-4 mt-4 rounded-lg bg-gray-50">
                                    <div className="flex items-center gap-3">
                                        <div className="w-12 h-12 overflow-hidden bg-white rounded-lg">
                                            {selectedLining ? (
                                                <img
                                                    src={selectedLining.fabric?.image || selectedLining.image}
                                                    alt={selectedLining.fabric?.name || "Custom Lining"}
                                                    className="object-contain w-full h-full p-1"
                                                />
                                            ) : selectedBody?.default_linings?.[0] ? (
                                                <img
                                                    src={selectedBody.default_linings[0].image}
                                                    alt="Default Lining"
                                                    className="object-contain w-full h-full p-1"
                                                />
                                            ) : (
                                                <div className="flex items-center justify-center w-full h-full text-gray-400">
                                                    <Palette className="w-5 h-5" />
                                                </div>
                                            )}
                                        </div>
                                        <div className="flex-1">
                                            <p className="text-sm font-medium text-gray-900">
                                                {selectedLining?.fabric?.name || "Default Lining"}
                                            </p>
                                            <p className="text-xs text-gray-500">
                                                {liningMode === "default" ? "Default lining" : `Custom: ${selectedLining?.fabric?.name || "Not selected"}`}
                                            </p>
                                        </div>
                                        {selectedLining && (
                                            <button
                                                onClick={() => handleDefaultLiningClick()}
                                                className="text-xs text-gray-500 underline hover:text-gray-700"
                                            >
                                                Reset to Default
                                            </button>
                                        )}
                                    </div>
                                </div> */}
                            </div>

                            {/* BUTTONS SECTION */}
                            {selectedBody?.body_type?.body_buttons && selectedBody.body_type.body_buttons.length > 0 && (
                                <div className="mb-6">
                                    <h3 className="mb-4 text-sm font-semibold tracking-wide text-gray-900 uppercase">
                                        Buttons
                                    </h3>

                                    <div className="grid grid-cols-3 gap-3">
                                        <ButtonOptionTile
                                            key="default-button"
                                            isSelected={!selectedButton}
                                            onClick={() => handleDefaultButtonClick()}
                                            image={null}
                                            label="Default"
                                            isLoading={false}
                                            isDefault={true}
                                        />

                                        {selectedBody.body_type.body_buttons.map((button) => (
                                            <ButtonOptionTile
                                                key={button.id}
                                                isSelected={selectedButton?.id === button.id}
                                                onClick={() => handleButtonSelect(button)}
                                                image={button.button_image?.diagram || button.image}
                                                label={button.button_image?.name || `Button ${button.id}`}
                                                isLoading={isButtonLoading && pendingButton?.id === button.id}
                                            />
                                        ))}
                                    </div>

                                    {/* <div className="p-4 mt-4 rounded-lg bg-gray-50">
                                        <div className="flex items-center gap-3">
                                            <div className="w-12 h-12 overflow-hidden bg-white rounded-lg">
                                                {selectedButton ? (
                                                    <img
                                                        src={selectedButton.button_image?.diagram || selectedButton.image}
                                                        alt={selectedButton.button_image?.name || "Button"}
                                                        className="object-contain w-full h-full p-1"
                                                    />
                                                ) : (
                                                    <div className="flex items-center justify-center w-full h-full text-gray-400">
                                                        <span className="text-xs font-medium">Default</span>
                                                    </div>
                                                )}
                                            </div>
                                            <div className="flex-1">
                                                <p className="text-sm font-medium text-gray-900">
                                                    {selectedButton?.button_image?.name || "Default Button"}
                                                </p>
                                                <p className="text-xs text-gray-500">
                                                    Current selection
                                                </p>
                                            </div>
                                        </div>
                                    </div> */}
                                </div>
                            )}
                        </div>
                    )}
                </div>

                {/* Vertical Tab Rail */}
                <div className="flex flex-col items-center w-20 gap-4 py-6 border-l border-gray-100 bg-gray-50">
                    <button
                        type="button"
                        className="flex items-center justify-center w-10 h-10 mb-2 text-gray-500 transition-colors rounded-lg hover:bg-gray-100"
                        aria-label="Menu"
                    >
                        <Menu className="w-5 h-5" />
                    </button>
                    <RailTab id="fabric" icon={Layers} label="Fabric" />
                    <RailTab id="style" icon={Scissors} label="Style" />
                    <RailTab id="accents" icon={Palette} label="Accents" />
                </div>
            </div>

            {/* CANVAS */}
            <div className="relative flex items-center justify-center flex-1 overflow-hidden bg-transparent">
                <div className="flex items-center justify-center w-full h-full">
                    <div
                        className="relative duration-500 bg-transparent rounded-xl"
                        style={{ width: "600px", height: "800px" }}
                    >
                        {layers.map((layer) => (
                            <CanvasLayer key={layer.id} layer={layer} />
                        ))}
                    </div>
                </div>

                {/* Loading indicator */}
                <LoadingIndicator loadingCount={activeLoads} />
            </div>

            {/* MODAL */}
{showLiningSidebar && (
    <div
        className="fixed inset-0 z-[1000] flex items-center justify-center bg-black/40 backdrop-blur-sm animate-in fade-in duration-200"
        onClick={(e) => {
            if (e.target === e.currentTarget) {
                setShowLiningSidebar(false);
            }
        }}
    >
        <div
            className="w-full max-w-md p-6 mx-4 duration-300 bg-white shadow-2xl rounded-xl animate-in zoom-in-95 slide-in-from-bottom-4"
        >
            {/* Header */}
            <div className="flex items-center justify-between mb-5">
                <div>
                    <h2 className="text-lg font-semibold text-gray-900">
                        Select Custom Lining
                    </h2>

                    <p className="mt-1 text-xs text-gray-500">
                        Choose your preferred lining
                    </p>
                </div>

                <button
                    onClick={() => setShowLiningSidebar(false)}
                    className="p-1.5 text-gray-400 transition-all duration-200 rounded-lg hover:text-gray-700 hover:bg-gray-100"
                >
                    <X className="w-5 h-5" />
                </button>
            </div>

            {/* Lining Options */}
            <div className="grid grid-cols-3 gap-3">
                {selectedFabric?.custom_linings?.map((lining) => {
                    const isSelected =
                        selectedLining?.id === lining.id;

                    return (
                        <button
                            key={lining.id}
                            onClick={() => handleLiningSelect(lining)}
                            className={`relative p-3 rounded-lg transition-all duration-300 ease-out ${
                                isSelected
                                    ? "shadow-md scale-[1.02]"
                                    : "hover:shadow-md hover:scale-[1.02]"
                            }`}
                        >
                            {/* Selected Indicator */}
                            {isSelected && (
                                <div className="absolute z-20 flex items-center justify-center w-5 h-5 text-xs text-white bg-gray-900 rounded-full top-2 right-2 animate-in fade-in zoom-in">
                                    ✓
                                </div>
                            )}

                            {/* Image */}
                            <div className="flex items-center justify-center w-full overflow-hidden bg-transparent rounded-md aspect-[4/3]">
                                <img
                                    src={
                                        lining.fabric?.image ||
                                        lining.image
                                    }
                                    alt={
                                        lining.fabric?.name ||
                                        "Lining"
                                    }
                                    className="object-contain w-full h-full"
                                />
                            </div>

                            {/* Label */}
                            <div className="mt-2">
                                <div className="text-xs font-medium leading-tight text-center text-gray-700">
                                    {lining.fabric?.name || "Lining"}
                                </div>
                            </div>
                        </button>
                    );
                })}
            </div>
        </div>
    </div>
)}
        </div>
    );
};

export default SuitDesigner;
