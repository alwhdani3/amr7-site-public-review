<div>
    @if($getState())
        <a href="{{ url('storage/'.$getState()) }}" target="_blank" class="text-primary-600 underline">
            تحميل الملف
        </a>
    @else
        <span class="text-gray-400">لا يوجد ملف</span>
    @endif
</div>
