<div class="row">
    <div class="col-md-6 mb-3">
        <label for="name" class="form-label">{{ get_label('name', 'Name') }}</label>
        <input type="text" class="form-control" name="name" id="name" required>
    </div>
    <div class="col-md-6 mb-3">
        <label for="category" class="form-label">{{ get_label('category', 'Category') }}</label>
        <select name="category" id="category" class="form-select" required>
            @foreach (\Plugins\Letter\Helper\LetterHelper::getLetterCategories() as $key => $category)
                <option value="{{ $key }}">{{ $category }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-12 mb-3">
        <label for="description" class="form-label">{{ get_label('description', 'Description') }}</label>
        <textarea name="description" id="description" class="form-control" rows="2"></textarea>
    </div>
    <div class="col-md-12 mb-3">
        <label for="content" class="form-label">{{ get_label('content', 'Content') }}</label>
        <textarea name="content" id="content" class="form-control tinymce-editor" rows="10" required></textarea>
    </div>
    <div class="col-md-12 mb-3">
        <label for="header_content" class="form-label">{{ get_label('header_content', 'Header Content') }}</label>
        <textarea name="header_content" id="header_content" class="form-control" rows="4"></textarea>
    </div>
    <div class="col-md-12 mb-3">
        <label for="footer_content" class="form-label">{{ get_label('footer_content', 'Footer Content') }}</label>
        <textarea name="footer_content" id="footer_content" class="form-control" rows="4"></textarea>
    </div>
    <div class="col-md-12">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" checked>
            <label class="form-check-label" for="is_active">{{ get_label('active', 'Active') }}</label>
        </div>
    </div>
</div>
