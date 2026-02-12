<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; margin: 2rem; }
        .prompt-form { margin-top: 2rem; max-width: 480px; }
        .prompt-form textarea { width: 100%; min-height: 80px; padding: 0.5rem; }
        .btn { padding: 0.5rem 1rem; background: #2563eb; color: #fff; border-radius: 0.375rem; border: none; cursor: pointer; }
        .btn:disabled { opacity: 0.6; cursor: not-allowed; }
        .status { margin-top: 1rem; color: #4b5563; }
        .image-wrapper { margin-top: 2rem; }
        .image-wrapper img { max-width: 512px; border-radius: 0.5rem; box-shadow: 0 10px 25px rgba(0,0,0,0.15); }
        .error { color: #b91c1c; margin-top: 0.5rem; white-space: pre-wrap; }
    </style>
</head>
<body>
    <h1>Dashboard</h1>
    <p>You are logged in via AISITE OAuth.</p>
    <p>Welcome, {{ auth()->user()->name ?? 'User' }}!</p>

    <div class="prompt-form">
        <h2>Generate an image via AISITE</h2>
        <p>Type a prompt and we&apos;ll ask AISITE to generate an image using the Nano Banana image pipeline.</p>

        <textarea id="prompt" placeholder="e.g. A futuristic city skyline at sunset with flying cars..."></textarea>
        <br>
        <button id="generateBtn" class="btn">Generate Image</button>

        <div id="status" class="status"></div>
        <div id="error" class="error"></div>

        <div id="imageContainer" class="image-wrapper" style="display: none;">
            <h3>Generated Image</h3>
            <img id="generatedImage" src="" alt="Generated image">
        </div>
    </div>

    <script>
        const btn = document.getElementById('generateBtn');
        const promptInput = document.getElementById('prompt');
        const statusEl = document.getElementById('status');
        const errorEl = document.getElementById('error');
        const imgContainer = document.getElementById('imageContainer');
        const imgEl = document.getElementById('generatedImage');

        async function generateImage() {
            const prompt = promptInput.value.trim();
            if (!prompt) {
                errorEl.textContent = 'Please enter a prompt.';
                return;
            }

            errorEl.textContent = '';
            statusEl.textContent = 'Generating image...';
            btn.disabled = true;

            try {
                const response = await fetch('{{ route('api.image.generate') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({ prompt }),
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.error || 'Image generation failed');
                }

                statusEl.textContent = 'Image generated successfully.';

                if (data.image_url) {
                    imgEl.src = data.image_url;
                    imgContainer.style.display = 'block';
                } else {
                    imgContainer.style.display = 'none';
                    statusEl.textContent = 'No image URL returned.';
                }
            } catch (e) {
                errorEl.textContent = e.message;
                statusEl.textContent = '';
                imgContainer.style.display = 'none';
            } finally {
                btn.disabled = false;
            }
        }

        btn.addEventListener('click', generateImage);
    </script>
</body>
</html>


