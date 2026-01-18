<div class="btn-group" role="group">
    <!-- View Button (Modal Trigger) -->
    <button type="button" class="btn btn-ghost-success view-category-btn" title="View"
            data-id="{{ $category->id }}"
            data-category='{
                "name": "{{ $category->name }}",
                "status": {{ $category->status }},
                "products_count": {{ $category->products_count ?? $category->products()->count() }},
                "created_at": "{{ $category->created_at ? $category->created_at->format('d-m-Y H:i') : 'N/A' }}",
                "updated_at": "{{ $category->updated_at ? $category->updated_at->format('d-m-Y H:i') : 'N/A' }}"
            }'>
        <i class="fa fa-eye"></i>
    </button>
    
    <!-- Edit Button (Modal Trigger) -->
    <button type="button" class="btn btn-ghost-primary edit-category-btn" title="Edit"
            data-id="{{ $category->id }}"
            data-category='{
                "name": "{{ $category->name }}",
                "status": {{ $category->status }}
            }'>
        <i class="fa fa-edit"></i>
    </button>
    
    <!-- Delete Button -->
    <form action="{{ route('productCategories.destroy', $category->id) }}" method="POST" style="display: inline;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-ghost-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this category?')">
            <i class="fa fa-trash"></i>
        </button>
    </form>
</div>