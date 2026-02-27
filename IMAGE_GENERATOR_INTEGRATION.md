# Image Generator Integration Guide

> **Purpose:** Build an image generator page in this app (`0auth-visual-tools`) that calls the `dev_ai` backend API instead of AI providers directly.

---

## Overview

The `dev_ai` app at `D:\Work\USA\new_dev_ai\dev_ai` is the backend. This app will be the frontend consumer. Generation requests go:

```
This App (0auth-visual-tools) → dev_ai API → AI Provider (OpenAI / Google / Stability AI / xAI)
```

---

## API Endpoints to Expose from dev_ai

These routes in `dev_ai/routes/web.php` need to be accessible (or mirrored as API routes) for this app to call.

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/image-generator-v2/provider/{providerId}/models` | Get active models for a provider |
| POST | `/image-generator-v2/generate` | Generate image(s) |
| GET | `/image-generator-v2/autocomplete` | Prompt autocomplete suggestions |
| GET | `/image-generator-v2/prompt-history` | User's past prompts |
| DELETE | `/image-generator-v2/prompt-history` | Clear prompt history |
| GET | `/image-generator-v2/prompt-templates` | Prompt library templates |
| GET | `/image-generator-v2/templates` | Image templates (predefined & user) |
| POST | `/image-generator-v2/templates` | Save user custom template |
| DELETE | `/image-generator-v2/templates` | Delete user template |
| POST | `/image-generator-v2/templates/{id}/favorite` | Toggle favorite template |
> **Minimum required for basic generation:** The `/generate` and `/provider/{id}/models` endpoints plus an endpoint to list all providers with their models (modify the existing `index()` or add a dedicated API route).

---

## Step-by-Step Plan

### Step 1 — Add API Routes in dev_ai

In `dev_ai/routes/api.php` (or modify `web.php`), expose JSON-returning endpoints accessible from external apps. The `index()` method already returns providers + models to the view — extract that data into a separate API route:

```php
// dev_ai/routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/image-generator/providers', [NewImageGenerationController::class, 'getProviders']);
    Route::get('/image-generator/provider/{providerId}/models', [NewImageGenerationController::class, 'getProviderModels']);
    Route::post('/image-generator/generate', [NewImageGenerationController::class, 'generate']);
    Route::get('/image-generator/prompt-history', [NewImageGenerationController::class, 'getPromptHistory']);
    Route::get('/image-generator/autocomplete', [NewImageGenerationController::class, 'getAutocompleteSuggestions']);
    Route::get('/image-generator/templates', [NewImageGenerationController::class, 'getTemplates']);
});
```

**New `getProviders()` method to add to `NewImageGenerationController`:**
```php
public function getProviders(): JsonResponse
{
    $providers = ImageProvider::where('is_active', true)
        ->with(['activeModels' => function ($q) {
            $q->select([
                'id', 'image_provider_id', 'name', 'model_identifier',
                'description', 'credits_per_image', 'pricing_matrix',
                'available_resolutions', 'available_styles', 'available_qualities',
                'available_output_formats', 'additional_parameters',
                'default_resolution', 'default_style', 'default_quality',
                'default_output_format', 'supports_negative_prompt',
                'supports_image_to_image', 'max_images_per_request', 'is_default',
            ])->orderBy('sort_order');
        }])
        ->orderBy('sort_order')
        ->get();

    return response()->json(['success' => true, 'data' => $providers]);
}
```

---

### Step 2 — Authentication Between Apps

Use Laravel Sanctum tokens. This app logs in to dev_ai and receives a token, then passes it as `Authorization: Bearer {token}` on every API call.

Alternatively, create a dedicated service account API key in dev_ai that this app uses for all requests on behalf of its own users.

---

### Step 3 — Build the Controller in This App

Create `app/Http/Controllers/ImageGeneratorController.php`:

```php
class ImageGeneratorController extends Controller
{
    private string $devAiBase;
    private string $devAiToken;

    public function __construct()
    {
        $this->devAiBase  = config('services.dev_ai.base_url');  // e.g. https://devai.yourdomain.com
        $this->devAiToken = config('services.dev_ai.api_token');
    }

    public function index()
    {
        // Fetch providers + models from dev_ai
        $response  = Http::withToken($this->devAiToken)->get("{$this->devAiBase}/api/image-generator/providers");
        $providers = $response->json('data', []);
        return view('image-generator.index', compact('providers'));
    }

    public function generate(Request $request)
    {
        // Forward to dev_ai
        $payload = $request->only([
            'model_id', 'prompt', 'negative_prompt', 'resolution',
            'style', 'quality', 'output_format', 'num_images',
            'guidance_scale', 'steps', 'seed', 'additional_parameters',
        ]);

        if ($request->hasFile('reference_images')) {
            // Multipart request
            $http = Http::withToken($this->devAiToken)->asMultipart();
            foreach ($request->file('reference_images') as $file) {
                $http->attach('reference_images[]', file_get_contents($file->path()), $file->getClientOriginalName());
            }
            foreach ($payload as $key => $value) {
                $http->attach($key, is_array($value) ? json_encode($value) : (string)$value);
            }
            $response = $http->post("{$this->devAiBase}/api/image-generator/generate");
        } else {
            $response = Http::withToken($this->devAiToken)
                ->post("{$this->devAiBase}/api/image-generator/generate", $payload);
        }

        return response()->json($response->json(), $response->status());
    }

    public function getProviderModels($providerId)
    {
        $response = Http::withToken($this->devAiToken)
            ->get("{$this->devAiBase}/api/image-generator/provider/{$providerId}/models");
        return response()->json($response->json());
    }

    public function getAutocompleteSuggestions(Request $request)
    {
        $response = Http::withToken($this->devAiToken)
            ->get("{$this->devAiBase}/api/image-generator/autocomplete", $request->only(['query']));
        return response()->json($response->json());
    }

    public function getPromptHistory(Request $request)
    {
        $response = Http::withToken($this->devAiToken)
            ->get("{$this->devAiBase}/api/image-generator/prompt-history", $request->only(['sort', 'limit']));
        return response()->json($response->json());
    }
}
```

---

### Step 4 — Add Routes in This App

In `routes/web.php`:

```php
Route::middleware('auth')->prefix('image-generator')->name('image-generator.')->group(function () {
    Route::get('/', [ImageGeneratorController::class, 'index'])->name('index');
    Route::post('/generate', [ImageGeneratorController::class, 'generate'])->name('generate');
    Route::get('/provider/{providerId}/models', [ImageGeneratorController::class, 'getProviderModels'])->name('provider.models');
    Route::get('/autocomplete', [ImageGeneratorController::class, 'getAutocompleteSuggestions'])->name('autocomplete');
    Route::get('/prompt-history', [ImageGeneratorController::class, 'getPromptHistory'])->name('prompt-history');
});
```

---

### Step 5 — Environment Config

Add to `.env`:

```env
DEV_AI_BASE_URL=https://your-devai-domain.com
DEV_AI_API_TOKEN=your-sanctum-token-here
```

Add to `config/services.php`:

```php
'dev_ai' => [
    'base_url'  => env('DEV_AI_BASE_URL'),
    'api_token' => env('DEV_AI_API_TOKEN'),
],
```

---

## Model Settings Panel — What to Build

This is the core UI. Below are all settings needed per provider/model, and the logic to show/hide each one.

---

### Settings That Are Always Shown

| Setting | Type | Description |
|---------|------|-------------|
| **Provider** | Card selection | Show all active providers; switching loads models |
| **Model** | Dropdown | Filtered by selected provider |
| **Prompt** | Textarea | Required, max 4000 chars |
| **Number of Images** | Number input | 1 to `model.max_images_per_request` (default 1–4) |
| **Aspect Ratio / Resolution** | Grid picker | From `model.available_resolutions` |

---

### Conditional Settings (show based on model data)

#### Quality
- **Show when:** `model.available_qualities` is not empty
- **Values from:** `model.available_qualities` (e.g. `["standard", "hd"]`)
- **Default:** `model.default_quality`
- **Providers:** OpenAI (DALL-E 3 → standard/hd; GPT-Image → standard/hd/auto)

#### Style
- **Show when:** `model.available_styles` is not empty
- **Values from:** `model.available_styles` (e.g. `["vivid", "natural"]` for DALL-E 3; `["photographic", "anime", "digital art", "3d-model", "comic-book", "fantasy-art", "line-art", "analog-film", "cinematic", "isometric", "low-poly", "origami", "pixel-art", "tile-texture"]` for Stability AI)
- **Default:** `model.default_style`

#### Output Format
- **Show when:** `model.available_output_formats` is not empty
- **Values:** `png`, `jpg`, `webp`
- **Default:** `model.default_output_format`

#### Negative Prompt
- **Show when:** `model.supports_negative_prompt === true`
- **Type:** Textarea
- **Providers with support:** Stability AI (all SD models)

#### Guidance Scale (CFG Scale)
- **Show when:** provider slug is `stable-diffusion`
- **Type:** Slider, range 1–20, step 0.5
- **Default:** 7.5
- **Sent as:** `additional_parameters.cfg_scale`

#### Steps
- **Show when:** provider slug is `stable-diffusion`
- **Type:** Slider, range 1–100, step 1
- **Default:** 30
- **Sent as:** `additional_parameters.steps`

#### Seed
- **Show when:** any model that supports it (check `model.additional_parameters` for a `seed` key, or always show for SD)
- **Type:** Number input with a "randomize" dice button
- **Optional:** Empty = random

---

### Dynamic Additional Parameters (from `model.additional_parameters`)

The `additional_parameters` field in each model is a JSON object defining extra controls. The frontend must iterate this object and render the correct control type.

```javascript
// Structure per parameter:
{
  "param_key": {
    "type": "checkbox" | "select" | "number" | "text",
    "label": "Human Readable Label",
    "options": ["option1", "option2"],   // for "select" type only
    "min": 1,                            // for "number" type only
    "max": 100,                          // for "number" type only
    "default": "default_value",
    "enabled_by_default": false          // for "checkbox" type
  }
}
```

**Known additional_parameters per provider:**

**Google Imagen:**
```json
{
  "person_generation": {
    "type": "select",
    "label": "Person Generation",
    "options": ["allow_adult", "disallow_adult"],
    "default": "allow_adult"
  },
  "text_in_image": {
    "type": "checkbox",
    "label": "Allow Text in Image",
    "enabled_by_default": true
  }
}
```

**Gemini (Nano Banana / Gemini 3-Pro):**
```json
{
  "enable_google_search": {
    "type": "checkbox",
    "label": "Enable Google Search Grounding",
    "enabled_by_default": false
  },
  "safety_level": {
    "type": "select",
    "label": "Safety Level",
    "options": ["minimal", "moderate", "strict"],
    "default": "moderate"
  }
}
```

**Stability AI (CFG/Steps are shown via provider slug; negative prompt via model flag):**
No additional_parameters beyond the dedicated CFG/Steps/Seed controls.

---

## JavaScript Logic for Model Settings

Below is the Alpine.js / vanilla JS data structure and methods needed. Port these from `dev_ai/resources/views/backend/image_generate/new_generator.blade.php`.

### State Object

```javascript
{
  // Provider & model
  providers: [],
  selectedProvider: null,        // provider ID (integer)
  selectedModel: null,           // model ID (integer)
  currentModelData: {},          // full model object from API

  // Prompt
  prompt: '',
  negativePrompt: '',

  // Core settings (populated from model defaults on model change)
  settings: {
    resolution: '',
    style: '',
    quality: '',
    output_format: 'png',
    num_images: 1,
  },

  // Advanced SD settings
  guidanceScale: 7.5,
  steps: 30,
  seed: '',

  // Dynamic additional params
  additionalParams: {},          // { param_key: current_value }
  additionalParamsEnabled: {},   // { param_key: boolean }  — for checkbox-gated params

  // Reference images (Gemini only)
  referenceImages: [],
  referenceImagesPreviews: [],

  // UI state
  loading: false,
  loadingProgress: 0,
  generatedImages: [],
  creditsLeft: 0,
}
```

### Key Functions

#### `selectProvider(providerId)`
```javascript
selectProvider(providerId) {
  this.selectedProvider = providerId;
  const provider = this.providers.find(p => p.id === providerId);
  if (provider && provider.active_models?.length > 0) {
    this.selectModel(provider.active_models[0].id);
  }
}
```

#### `selectModel(modelId)` / `onModelChange()`
```javascript
onModelChange() {
  const provider = this.providers.find(p => p.id === this.selectedProvider);
  const model = provider?.active_models?.find(m => m.id === this.selectedModel);
  if (!model) return;

  this.currentModelData = model;

  // Apply defaults
  this.settings.resolution   = model.default_resolution   || model.available_resolutions?.[0] || '';
  this.settings.style        = model.default_style        || model.available_styles?.[0]       || '';
  this.settings.quality      = model.default_quality      || model.available_qualities?.[0]    || '';
  this.settings.output_format = model.default_output_format || 'png';
  this.settings.num_images   = 1;

  // SD defaults
  if (this.supportsGuidanceScale()) { this.guidanceScale = 7.5; }
  if (this.supportsSteps())         { this.steps = 30; }

  this.initializeAdditionalParams();
}
```

#### `initializeAdditionalParams()`
```javascript
initializeAdditionalParams() {
  const params = this.currentModelData.additional_parameters || {};
  this.additionalParams        = {};
  this.additionalParamsEnabled = {};

  for (const [key, config] of Object.entries(params)) {
    if (config.type === 'checkbox') {
      this.additionalParamsEnabled[key] = config.enabled_by_default ?? false;
      this.additionalParams[key]        = config.enabled_by_default ?? false;
    } else {
      this.additionalParamsEnabled[key] = true;
      this.additionalParams[key]        = config.default ?? (config.options?.[0] ?? '');
    }
  }
}
```

#### `supportsGuidanceScale()` / `supportsSteps()` / `supportsNegativePrompt()`
```javascript
supportsGuidanceScale() {
  return this.providers.find(p => p.id === this.selectedProvider)?.slug === 'stable-diffusion';
}
supportsSteps() {
  return this.supportsGuidanceScale();
}
supportsNegativePrompt() {
  return !!this.currentModelData.supports_negative_prompt;
}
```

#### `getEnabledAdditionalParams()`
```javascript
getEnabledAdditionalParams() {
  const result = {};
  const params = this.currentModelData.additional_parameters || {};
  for (const [key, config] of Object.entries(params)) {
    if (config.type === 'checkbox') {
      if (this.additionalParamsEnabled[key]) {
        result[key] = this.additionalParams[key];
      }
    } else {
      result[key] = this.additionalParams[key];
    }
  }
  return result;
}
```

#### `calculateCost()`
```javascript
calculateCost() {
  const model = this.currentModelData;
  if (!model?.id) return 0;

  if (model.pricing_matrix) {
    const matrix = typeof model.pricing_matrix === 'string'
      ? JSON.parse(model.pricing_matrix) : model.pricing_matrix;
    const quality = this.settings.quality || 'standard';
    const res     = this.settings.resolution || '';
    return (matrix[quality]?.[res] ?? model.credits_per_image ?? 0) * this.settings.num_images;
  }

  return (model.credits_per_image ?? 0) * this.settings.num_images;
}
```

#### `generateImage()`
```javascript
async generateImage() {
  if (!this.prompt.trim()) return alert('Please enter a prompt.');
  if (!this.selectedModel)  return alert('Please select a model.');

  this.loading = true;
  this.loadingProgress = 0;
  this.simulateProgress();

  const hasRefImages = this.referenceImages.length > 0;

  let body, headers = {};

  if (hasRefImages) {
    const fd = new FormData();
    fd.append('model_id', this.selectedModel);
    fd.append('prompt',   this.prompt);
    if (this.negativePrompt) fd.append('negative_prompt', this.negativePrompt);
    fd.append('resolution',    this.settings.resolution);
    fd.append('style',         this.settings.style);
    fd.append('quality',       this.settings.quality);
    fd.append('output_format', this.settings.output_format);
    fd.append('num_images',    this.settings.num_images);
    if (this.supportsGuidanceScale()) fd.append('guidance_scale', this.guidanceScale);
    if (this.supportsSteps())         fd.append('steps', this.steps);
    if (this.seed)                    fd.append('seed', this.seed);
    fd.append('additional_parameters', JSON.stringify(this.getEnabledAdditionalParams()));
    this.referenceImages.forEach(f => fd.append('reference_images[]', f));
    body = fd;
  } else {
    headers['Content-Type'] = 'application/json';
    body = JSON.stringify({
      model_id:              this.selectedModel,
      prompt:                this.prompt,
      negative_prompt:       this.negativePrompt,
      resolution:            this.settings.resolution,
      style:                 this.settings.style,
      quality:               this.settings.quality,
      output_format:         this.settings.output_format,
      num_images:            this.settings.num_images,
      guidance_scale:        this.supportsGuidanceScale() ? this.guidanceScale : undefined,
      steps:                 this.supportsSteps()         ? this.steps         : undefined,
      seed:                  this.seed || undefined,
      additional_parameters: this.getEnabledAdditionalParams(),
    });
  }

  try {
    const res  = await fetch('/image-generator/generate', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, ...headers },
      body,
    });
    const data = await res.json();

    if (data.success) {
      this.generatedImages = [...(data.data || []), ...this.generatedImages];
      this.creditsLeft     = data.credits_left ?? this.creditsLeft;

    } else {
      alert(data.message || 'Generation failed.');
    }
  } catch (e) {
    alert('Request failed. Please try again.');
  } finally {
    this.loading         = false;
    this.loadingProgress = 100;
  }
}
```

#### `simulateProgress()`
```javascript
simulateProgress() {
  const steps = [
    { target: 15,  delay: 300  },
    { target: 35,  delay: 800  },
    { target: 55,  delay: 1500 },
    { target: 72,  delay: 2000 },
    { target: 85,  delay: 1000 },
    { target: 93,  delay: 2000 },
  ];
  let i = 0;
  const tick = () => {
    if (i >= steps.length || !this.loading) return;
    const { target, delay } = steps[i++];
    this.loadingProgress = target;
    setTimeout(tick, delay);
  };
  tick();
}
```

---

## Provider-Specific API Parameters

### OpenAI (DALL-E 3, GPT-Image-1)

**Required request fields:**
```
model_id, prompt, num_images, resolution, quality, style, output_format
```

**Notes:**
- Quality: `standard` | `hd`
- Style: `vivid` | `natural`
- Sizes for DALL-E 3: `1024x1024`, `1024x1536`, `1536x1024`
- Returns: image URLs (expire ~1 hour) — dev_ai downloads and stores to Azure

**Extra helper needed in dev_ai:** None beyond the existing `generateOpenAI()`.

---

### Google Imagen

**Required request fields:**
```
model_id, prompt, num_images, resolution (as aspect ratio like "1:1")
```

**Optional additional_parameters:**
- `person_generation`: `allow_adult` | `disallow_adult`
- `text_in_image`: boolean

**Notes:**
- `num_images` maps to `sampleCount` in Google API (max 4)
- Returns base64 PNG images

**Extra helper needed:** None; handled by `generateGoogle()` in dev_ai.

---

### Gemini / Nano Banana (Gemini 2.5-Flash, Gemini 3-Pro)

**Required request fields:**
```
model_id, prompt
```

**Optional fields:**
```
reference_images[] (multipart), conversation_id, additional_parameters
```

**additional_parameters:**
- `enable_google_search`: boolean (3-Pro only)
- `safety_level`: `minimal` | `moderate` | `strict`

---

### Stability AI (Stable Diffusion)

**Required request fields:**
```
model_id, prompt, resolution (as aspect_ratio like "1:1")
```

**Optional fields:**
```
negative_prompt, guidance_scale (cfg_scale), steps, seed, style, output_format
```

**Notes:**
- `resolution` sent as aspect ratio: `1:1`, `16:9`, `3:2`, `4:3`, `9:16`, `2:3`
- `cfg_scale` range: 1–20 (SD Ultra doesn't support it)
- `steps` range: 1–100
- `seed`: integer (empty = random)
- Style options: `photographic`, `anime`, `digital art`, `3d-model`, `comic-book`, `fantasy-art`, `line-art`, `analog-film`, `cinematic`, `isometric`, `low-poly`, `origami`, `pixel-art`, `tile-texture`
- Returns: PNG images as binary → dev_ai converts and stores

**Extra helper needed:** None; `StableDiffusionService` in dev_ai handles it.

---

### xAI (Grok)

**Required request fields:**
```
model_id, prompt, num_images
```

**Optional:**
```
resolution (size), output_format
```

**Notes:**
- Simpler API; returns URL-based images
- dev_ai downloads & stores them

**Extra helper needed:** None.

---

## Settings UI Component Checklist

Build each of these as a reusable component or section:

- [ ] **Provider selector** — card grid, highlight selected, clicking calls `selectProvider()`
- [ ] **Model dropdown** — filtered list, `onChange` calls `onModelChange()`
- [ ] **Resolution/Aspect ratio picker** — grid of buttons from `available_resolutions`; show "Show more" after first 6
- [ ] **Quality dropdown** — rendered only when `available_qualities` is not empty
- [ ] **Style dropdown** — rendered only when `available_styles` is not empty
- [ ] **Output format radio/dropdown** — rendered only when `available_output_formats` is not empty
- [ ] **Num images input** — 1 to `max_images_per_request`
- [ ] **Guidance Scale slider** — shown only when `supportsGuidanceScale()`
- [ ] **Steps slider** — shown only when `supportsSteps()`
- [ ] **Seed input + randomize button** — optional; shown for SD and models with seed support
- [ ] **Negative prompt textarea** — shown only when `supportsNegativePrompt()`
- [ ] **Dynamic additional params** — rendered from `model.additional_parameters` JSON; render checkbox / select / number inputs based on `type` field
- [ ] **Reference image upload** — shown only for Gemini (Nano Banana) models; `model.supports_image_to_image === true`
- [ ] **Credits cost preview** — runs `calculateCost()` reactively

---

## Credit System Notes

- Stored on the user in dev_ai's database
- The `/generate` endpoint validates and deducts credits server-side
- Response includes `credits_left` — update it in the UI after each generation
- Display current credits on page load (pass from server or fetch via separate endpoint)
- **OpenAI pricing:** dynamic via `pricing_matrix[quality][resolution]`
- **Others:** flat `credits_per_image × num_images`

---

## Image Storage Notes

- dev_ai stores all generated images to **Azure Blob Storage**
- Returned image URLs in the API response point to Azure CDN URLs
- No need to handle storage in this app — just display the URLs

---

## Summary of What to Build in This App

1. **`ImageGeneratorController`** — proxies all requests to dev_ai
2. **`routes/web.php` entries** — `/image-generator` and sub-routes
3. **Blade view** — single page with Alpine.js
4. **Alpine.js state** — providers, models, settings, params (as documented above)
5. **Settings panel** — provider picker, model picker, and all conditional controls
6. **Generate button** — calls `generateImage()` with progress simulation
7. **Results gallery** — display returned images with download option
8. **`.env` config** — `DEV_AI_BASE_URL` + `DEV_AI_API_TOKEN`

**Optional but recommended:**
- Prompt autocomplete
- Prompt history sidebar
- Template browser
