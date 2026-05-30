<?php
namespace App\Livewire;
use Livewire\Component;
use App\Models\Post;
use App\Traits\HasSEO;

class BlogIndex extends Component
{
    use HasSEO;

    public int $perPage = 9;
    public int $total = 0;

public function mount()
{
    // حساب الإجمالي الحقيقي للمقالات المنشورة فقط
    $this->total = Post::where('is_published', true)->count();
    
    $this->setSeo(
        __('blog.seo_title'),
        __('blog.seo_description'),
    );
}
    public function loadMore()
    {
        $this->perPage += 6;
    }

    public function render()
    {
        return view('livewire.blog-index', [
            'articles' => Post::where('is_published', true)
                ->latest('published_at')
                ->take($this->perPage)
                ->get(),
        ]);
    }
}
