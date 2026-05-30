@props(['title' => '', 'description' => ''])



<div class="text-center space-y-3 animate__animated animate__fadeInDown">

    

    <h1 class="text-3xl md:text-4xl font-black tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-slate-950 via-slate-800 to-teal-800">

        {{ $title }}

    </h1>



    @if($description)

     

        <p class="text-sm md:text-base leading-relaxed text-slate-600 font-medium max-w-sm mx-auto opacity-80">

            {{ $description }}

        </p>

    @endif

    <div class="w-16 h-1.5 bg-gradient-to-r from-teal-500 to-teal-600 mx-auto rounded-full mt-4 shadow-sm shadow-teal-100"></div>

</div>



