<x-app-layout>
    <x-slot name="title">Edit SHU {{ $period->year }}</x-slot>
    <x-page-header title="Edit Parameter SHU {{ $period->year }}" description="Perubahan parameter akan mengembalikan status ke DRAFT dan menghapus hasil kalkulasi sebelumnya."></x-page-header>
    @include('shu._form', ['action'=>route('shu.update',$period),'method'=>'PUT','cancelUrl'=>route('shu.show',$period)])
</x-app-layout>
