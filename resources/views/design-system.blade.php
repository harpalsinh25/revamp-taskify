<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Design System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
<div class="p-6 max-w-7xl mx-auto space-y-12">
    <!-- Typography -->
    <section class="space-y-4">
        <h2 class="text-2xl font-bold text-gray-900 border-b pb-2">Typography</h2>
        <div class="space-y-2">
            <h1 class="text-4xl font-extrabold text-gray-900">Heading 1</h1>
            <h2 class="text-3xl font-bold text-gray-900">Heading 2</h2>
            <h3 class="text-2xl font-semibold text-gray-900">Heading 3</h3>
            <h4 class="text-xl font-medium text-gray-900">Heading 4</h4>
            <p class="text-base text-gray-600">Regular paragraph text. Used for general descriptions and body content.</p>
            <p class="text-sm text-gray-500">Small paragraph text. Used for secondary information and hints.</p>
        </div>
    </section>

    <!-- Buttons -->
    <section class="space-y-4">
        <h2 class="text-2xl font-bold text-gray-900 border-b pb-2">Buttons</h2>
        <div class="flex flex-wrap gap-4 items-center">
            <button class="px-4 py-2 bg-blue-600 text-gray-50 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 transition-colors text-sm font-medium">Primary Button</button>
            <button class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 focus:ring-4 focus:ring-gray-100 transition-colors text-sm font-medium">Secondary Button</button>
            <button class="px-4 py-2 bg-red-600 text-gray-50 rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-red-300 transition-colors text-sm font-medium">Danger Button</button>
            <button class="px-4 py-2 bg-green-600 text-gray-50 rounded-lg hover:bg-green-700 focus:ring-4 focus:ring-green-300 transition-colors text-sm font-medium">Success Button</button>
            <button class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 transition-colors text-sm font-medium">Outline Button</button>
            <button class="px-4 py-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors text-sm font-medium">Ghost Button</button>
        </div>
    </section>

    <!-- Form Elements -->
    <section class="space-y-4">
        <h2 class="text-2xl font-bold text-gray-900 border-b pb-2">Form Elements</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Input text -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">Text Input</label>
             <input 
  type="text" 
  placeholder="Enter text..." 
  class="w-full px-3 py-2 bg-transparent border border-gray-200 rounded-md shadow-sm text-sm 
         hover:bg-[#f5f5f9] hover:cursor-pointer 
         focus:bg-[#f5f5f9] 
         focus:border-gray-200 
         focus:outline-none 
         focus:ring-0 
         focus:shadow-none 
         transition-all">
            </div>

            <!-- Input Error -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">Input with Error</label>
                <input type="text" value="Invalid input" class="w-full px-3 py-2 bg-transparent border border-red-500 rounded-md shadow-sm text-sm text-red-900 placeholder-red-300 hover:bg-red-50 hover:cursor-pointer focus:!bg-red-50 focus:!border-red-500 focus:!ring-0 focus:!outline-none focus:!shadow-none transition-all">
                <p class="text-xs text-red-600 mt-1">This field is required.</p>
            </div>

            <!-- Select -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">Select Box</label>
                <select class="w-full px-3 py-2 bg-transparent border border-gray-200 rounded-md shadow-sm text-sm hover:bg-[#f5f5f9] hover:cursor-pointer focus:bg-[#f5f5f9] focus:border-gray-200 focus:ring-0 focus:outline-none transition-all">
                    <option>Option 1</option>
                    <option>Option 2</option>
                    <option>Option 3</option>
                </select>
            </div>

            <!-- Selection Field (TomSelect style) -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">Selection Field (TomSelect)</label>
                <div class="w-full px-3 py-2 bg-transparent border border-gray-200 rounded-md shadow-sm text-sm hover:bg-[#f5f5f9] hover:cursor-pointer focus-within:bg-[#f5f5f9] focus-within:border-gray-200 transition-all flex flex-wrap items-center gap-1.5 min-h-[38px]">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-gray-100 border border-gray-200 rounded text-xs text-gray-700">
                        Selected Item 1
                        <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors">&times;</button>
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-gray-100 border border-gray-200 rounded text-xs text-gray-700">
                        Selected Item 2
                        <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors">&times;</button>
                    </span>
                    <input type="text" placeholder="Type to search" class="flex-1 min-w-[80px] bg-transparent border-none outline-none text-sm text-gray-700 placeholder-gray-400 p-0">
                </div>
            </div>

            <!-- Selection Field Empty -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">Selection Field (Empty)</label>
                <div class="w-full px-3 py-2 bg-transparent border border-gray-200 rounded-md shadow-sm text-sm hover:bg-[#f5f5f9] hover:cursor-pointer focus-within:bg-[#f5f5f9] focus-within:border-gray-200 transition-all flex items-center min-h-[38px]">
                    <input type="text" placeholder="Select users..." class="flex-1 bg-transparent border-none outline-none text-sm text-gray-700 placeholder-gray-400 p-0">
                </div>
            </div>

            <!-- Textarea -->
            <div class="space-y-1 md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Textarea</label>
                <textarea rows="4" placeholder="Enter description..." class="w-full px-3 py-2 bg-transparent border border-gray-200 rounded-md shadow-sm text-sm hover:bg-[#f5f5f9] hover:cursor-pointer focus:bg-[#f5f5f9] focus:border-gray-200 focus:ring-0 focus:outline-none transition-all"></textarea>
            </div>

            <!-- Checkbox & Radio -->
            <div class="flex gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 bg-transparent">
                    <span class="text-sm text-gray-700">Checkbox</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="radio_group" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500 bg-transparent">
                    <span class="text-sm text-gray-700">Radio 1</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="radio_group" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500 bg-transparent">
                    <span class="text-sm text-gray-700">Radio 2</span>
                </label>
            </div>
        </div>
    </section>

    <!-- Cards -->
    <section class="space-y-4">
        <h2 class="text-2xl font-bold text-gray-900 border-b pb-2">Cards</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Basic Card -->
            <div class="bg-transparent rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-black/5">
                    <h3 class="text-lg font-semibold text-gray-900">Card Header</h3>
                </div>
                <div class="p-6 text-gray-600 text-sm bg-transparent">
                    This is a basic card component. It has a header, a body, and a subtle shadow.
                </div>
                <div class="px-6 py-4 border-t border-gray-200 bg-black/5 flex justify-end gap-2">
                    <button class="px-4 py-2 text-gray-600 hover:bg-gray-200 rounded-lg text-sm font-medium transition-colors">Cancel</button>
                    <button class="px-4 py-2 bg-blue-600 text-gray-50 rounded-lg hover:bg-blue-700 text-sm font-medium transition-colors">Save</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Badges & Tags -->
    <section class="space-y-4">
        <h2 class="text-2xl font-bold text-gray-900 border-b pb-2">Badges</h2>
        <div class="flex flex-wrap gap-2">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Primary</span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Secondary</span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Success</span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Danger</span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Warning</span>
        </div>
    </section>

    <!-- Alerts -->
    <section class="space-y-4">
        <h2 class="text-2xl font-bold text-gray-900 border-b pb-2">Alerts</h2>
        <div class="space-y-3">
            <div class="p-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-lg text-blue-800 text-sm flex items-start gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                <span><strong>Info!</strong> This is an informational alert message.</span>
            </div>
            <div class="p-4 bg-green-50 border-l-4 border-green-500 rounded-r-lg text-green-800 text-sm flex items-start gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                <span><strong>Success!</strong> Your action has been completed successfully.</span>
            </div>
            <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg text-red-800 text-sm flex items-start gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                <span><strong>Error!</strong> Something went wrong. Please try again.</span>
            </div>
        </div>
    </section>
     <div class="col-md-12 mb-3">
                    <label for="title" class="form-label"><?= get_label('title', 'Title') ?> <span
                            class="asterisk">*</span></label>
                    <input class="form-control w-full px-3 py-2 bg-white border border-[#d9dee3] rounded-md text-sm text-[#566a7f]  transition-all duration-150 hover:bg-[#f5f5f9] focus:!bg-[#f5f5f9] focus:!border-[#c9d0d6] focus:!ring-0 focus:!outline-none focus:placeholder-transparent" type="text" name="title"
                        placeholder="<?= get_label('please_enter_title', 'Please enter title') ?>"
                        value="{{ old('title') }}">
                </div>

    <!-- Modal (Static Preview) -->
    <section class="space-y-4">
        <h2 class="text-2xl font-bold text-gray-900 border-b pb-2">Modal</h2>
        <div class="relative bg-gray-900/50 p-6 rounded-xl flex items-center justify-center min-h-[300px]">
            <div class="bg-gray-50 rounded-xl shadow-xl w-full max-w-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Modal Title</h3>
                    <button class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-6 text-sm text-gray-600 bg-transparent">
                    This is the modal body content. You can place forms, information, or any other elements here.
                </div>
                <div class="px-6 py-4 bg-black/5 flex justify-end gap-3 border-t border-gray-200">
                    <button class="px-4 py-2 text-gray-700 bg-transparent border border-gray-300 hover:bg-gray-100 rounded-lg text-sm font-medium transition-colors shadow-sm">Close</button>
                    <button class="px-4 py-2 bg-blue-600 text-gray-50 rounded-lg hover:bg-blue-700 text-sm font-medium transition-colors shadow-sm">Confirm</button>
                </div>
            </div>
        </div>
    </section>
</div>
</body>
</html>
