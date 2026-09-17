<x-app-layout>
    <x-slot name="title">Buat Periode SHU</x-slot>
    <x-page-header title="Buat Periode SHU" description="Siapkan parameter SHU sebelum dilakukan kalkulasi."></x-page-header>
    @include('shu._form', ['action'=>route('shu.store'),'method'=>'POST','cancelUrl'=>route('shu.index')])
</x-app-layout>
