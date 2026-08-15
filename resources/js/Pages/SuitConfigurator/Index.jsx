import { Head } from '@inertiajs/react'

export default function SuitConfigurator({ fabrics }) {
  return (
    <>
      <Head title="Image Debug" />

      <div className="max-w-3xl">
        <h1 className="mb-6 text-2xl font-bold">RAW IMAGE DEBUG</h1>

        {fabrics.map(fabric =>
          fabric.bodies.map(body => (
            <div key={body.id} className="mb-10">
              <p className="text-sm font-semibold">
                {body.body_name}
              </p>

              <p className="mb-2 text-xs text-red-600 break-all">
                {String(body.image)}
              </p>

              <img
                src={body.image}
                alt=""
                style={{
                  // width: '600px',
                  // height: '300px',
                  border: '2px solid red',
                  display: 'block',
                }}
                onError={(e) => {
                  console.error('IMAGE ERROR:', body.image)
                }}
              />
            </div>
          ))
        )}
      </div>
    </>
  )
}
