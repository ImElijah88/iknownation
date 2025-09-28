'use client';

import { useState } from 'react';

export default function Page() {
  const [text, setText] = useState('');
  const [presentation, setPresentation] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [successMessage, setSuccessMessage] = useState(null);

  const generatePresentation = async () => {
    setLoading(true);
    setError(null);
    setSuccessMessage(null);
    setPresentation(null);
    try {
      const response = await fetch('http://localhost:5000/generate_presentation', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ text }),
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.error || 'Failed to generate presentation');
      }

      const data = await response.json();
      setPresentation(data);
      setSuccessMessage('Presentation generated successfully!');
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="container mx-auto p-4">
      <h1 className="text-3xl font-bold mb-4">Presentation Generator</h1>
      <textarea
        value={text}
        onChange={(e) => setText(e.target.value)}
        placeholder="Enter text for your presentation..."
        rows="10"
        className="w-full p-2 border border-gray-300 rounded-md mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500"
      />
      <button
        onClick={generatePresentation}
        disabled={loading || !text}
        className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md disabled:opacity-50"
      >
        {loading ? 'Generating...' : 'Generate Presentation'}
      </button>

      {successMessage && <p className="text-green-500 mt-4">{successMessage}</p>}
      {error && <p className="text-red-500 mt-4">Error: {error}</p>}

      {presentation && (
        <div className="mt-8">
          <h2 className="text-2xl font-bold mb-4">{presentation.title}</h2>
          <div className="space-y-4">
            {presentation.slides.map((slide, index) => (
              <div key={index} className="bg-gray-100 p-4 rounded-md shadow-sm">
                <h3 className="text-xl font-semibold mb-2">{slide.title}</h3>
                <p>{slide.content}</p>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}
