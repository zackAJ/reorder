<?php

use App\Models\Task;
use function Livewire\Volt\{state};

$state=state(["tasks","name","priority"]);

$save = fn () => Task::create(["name"=>$this->name,"priority"=>$this->priority]);
$clear = function (){
  $this->name="";
  $this->priority="";
}
?>

<div class="px-12 pt-16">
  <h1 class="text-4xl m-6">Task management</h1>
  <div class="flex flex-col  items-center">

    <form wire:submit.prevent="save" class="flex flex-col gap-6">
      <label for="name" class="text-lg font-bold flex justify-between gap-6 min-w-[300px]">name
        <input
          class="rounded-lg text-black" type="text" id="name" name="name" wire:model="name">
        </label>

      <label for="priority" class="text-lg font-bold flex justify-between gap-6 min-w-[300px]">priority
        <input
          class="rounded-lg text-black" type="text" id="priority" name="priority" wire:model="priority">
        </label>
      <div class="flex gap-14">
        <button  class="rounded-lg bg-green-700 px-2 py-1 hover:opacity-80">Save</button>
        <button wire:click="clear" class="rounded-lg bg-red-700 px-2 py-1 hover:opacity-80">Clear</button>
      </div>
    </form>
    <ul class="block">
      @forelse  (Task::all() as $task)
      <li class="block" >{{ $task->name }}</li>
      @empty
      <p>No Task Mr.Lazy...</p>
      @endforelse 
    </ul>
  </div>
</div>
