<?php

use App\Models\Task;
use function Livewire\Volt\{state};

state([
    'tasks' => Task::orderBy('priority', 'ASC')
        ->orderBy('order', 'DESC')
        ->orderBy('created_at', 'ASC')
        ->get(),
]);
state(['name', 'priority']);

$save = function () {
    $priority = $this->tasks->isEmpty() ? 'a' : $this->tasks->last()->priority . 'a';

    $new_task = Task::create(['name' => htmlspecialchars($this->name), 'priority' => $priority, 'order' => 'a']);
    $this->tasks = Task::orderBy('priority', 'asc')
        ->orderBy('order', 'DESC')
        ->orderBy('created_at', 'ASC')
        ->get();
    $this->name = '';
};

$clear = function () {
    $this->name = '';
};

$delete = function ($id) {
    $this->tasks->find($id)->delete();
    $this->tasks = Task::orderBy('priority', 'asc')
        ->orderBy('order', 'DESC')
        ->orderBy('created_at', 'ASC')
        ->get();
};
$update = function ($id, $new_name) {
    $this->tasks->find($id)->update(['name' => htmlspecialchars($new_name)]);
    $this->tasks = Task::orderBy('priority', 'asc')
        ->orderBy('order', 'DESC')
        ->orderBy('created_at', 'ASC')
        ->get();
};

$swap = function (Task $task_one, Task $task_two = null) {
    if ($task_one == $task_two) {
        return;
    }

    if (!$task_two->name) {
        $task_latest = Task::orderBy('priority', 'DESC')
            ->orderBy('order', 'ASC')
            ->orderBy('created_at', 'DESC')
            ->first();
        $task_one->update([
            'priority' => $task_latest->priority . 'a',
            'order' => 'a',
        ]);
    } else {
        if ($task_one->priority >= $task_two->priority) {
            $p = $task_two->order . 'a';
            $target = Task::where(['priority' => $task_two->priority, 'order' => $p])->first();
            if ($target) {
                $task_one->update([
                    'priority' => $task_two->priority,
                    'order' => $target->order,
                ]);

                $this->swap($target, $task_one);
            } else {
                $task_one->update([
                    'priority' => $task_two->priority,
                    'order' => $task_two->order . 'a',
                ]);
            }
        } else {
            $task_one->update([
                'priority' => $task_two->priority,
                'order' => $task_two->order,
            ]);
            $p = $task_two->priority . 'a';
            $target = Task::where(['priority' => $p, 'order' => 'a'])->first();
            if ($target) {
                $this->swap($task_two, $target);
            } else {
                $task_two->update([
                    'priority' => $p,
                    'order' => 'a',
                ]);
            }
        }
    }
    $this->tasks = Task::orderBy('priority', 'ASC')
        ->orderBy('order', 'DESC')
        ->orderBy('created_at', 'ASC')
        ->get();
};
?>

<div class="px-12 pt-16">


  <h1 class="text-4xl m-6 text-center">Task management</h1>
  <div class="flex flex-col  items-center">
    <script defer>
      const escapeHTML = str =>
        str.replace(
          /[&<>'"]/g,
          tag =>
          ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            "'": '&#39;',
            '"': '&quot;'
          } [tag] || tag)
        );
    </script>
    <main x-data="{
        decode: escapeHTML
    }" class="w-full flex flex-col items-center">
      <form wire:submit.prevent="save" class="flex flex-col gap-6">
        <label for="name" class="text-lg font-bold flex justify-between gap-6 min-w-[300px]">Task
          <input required class="rounded-lg text-black px-1" type="text" id="name" name="name"
            wire:model="name">
        </label>
        <div class="flex gap-14 justify-center">
          <button class="rounded-lg bg-green-700 px-2 py-1 hover:opacity-80">Save</button>
          <button wire:click.prevent="clear" class="rounded-lg bg-red-500 px-2 py-1 hover:opacity-80">Clear</button>
        </div>
      </form>
      <table x-data="{
          edit_mode: [],
          toggle(index) { this.edit_mode[index] = !this.edit_mode[index] },
          dropped_id: 4
      }" class="mt-10 w-full max-w-[500px] text-center">

        @forelse  ($tasks as $index => $task)
          @once
            <tr class="w-full">
              <th class="px-2">Order</th>
              <th class="px-2">ID</th>
              <th>Name</th>
              <th>Actions</th>
            </tr>
          @endonce

          <tr x-init="edit_mode[{{ $index }}] = false" draggable="true" x-data="{ drag_over: false, new_name: '' }"
            :class="{ 'w-full': true, 'border-t-[2px] border-t-white ': drag_over }" @dragenter.prevent="drag_over=true"
            @dragleave.prevent="drag_over=false"
            @dragstart="
              dropped_id =parseInt(event.currentTarget.children[1].innerText);
            "
            @dragover.prevent @dragend.prevent
            @drop.prevent="
            drag_over=false;
            if(dropped_id!={{ $task->id }})
            Livewire.first().swap(dropped_id,{{ $task->id }});
            ">
            <td class='flex justify-center'>
              <p class="text-lg font-bold text-red-900 rounded-full bg-white px-2">{{ $index + 1 }}</p>
            </td>

            <td>
              <p class="text-md font-bold text-white rounded-full  px-2">{{ $task->id }}</p>
            </td>

            <td x-show="!edit_mode[{{ $index }}]" class="p-2 w-full break-words max-w-[200px]">
              {{ htmlspecialchars_decode($task->name) }}
            </td>

            <td x-show="edit_mode[{{ $index }}]" class="p-2">
              <input class="rounded-lg text-black px-2 font-bold w-[120px]" type="text" id="name" name="name"
                x-model="new_name">
            </td>

            <td class="py-2 min-w-[120px] flex gap-1">

              <button
                x-on:click="function (){
              new_name=(s => s.raw)`{{ htmlspecialchars_decode($task->name) }}`[0];
              toggle({{ $index }});
              }"
                class="rounded-lg bg-blue-700 px-2 py-1 hover:opacity-80"
                x-text="edit_mode[{{ $index }}] ? 'Clear' : 'Edit' " />


              <button x-show="!edit_mode[{{ $index }}]" wire:click="delete({{ $task->id }})"
                class="rounded-lg bg-red-700 px-2 py-1 hover:opacity-80">Delete</button>

              <button x-show="edit_mode[{{ $index }}]" wire:click="update({{ $task->id }},new_name)"
                x-on:click="toggle({{ $index }})" class="rounded-lg bg-green-700 px-2 py-1 hover:opacity-80">
                Save
              </button>

            </td>
          </tr>

        @empty
          <p class="mt-8">No Tasks Mr.Lazy...</p>
        @endforelse
        @if (!$tasks->isEmpty())
          <tr x-data="{ drag_over: false }" :class="{ 'w-full h-[40px]': true, 'border-t-[2px] border-t-white ': drag_over }"
            @dragenter.prevent="drag_over=true" @dragleave.prevent="drag_over=false"
            @dragstart="
              dropped_id =0;
            " @dragover.prevent @dragend.prevent
            @drop.prevent="
            drag_over=false;
            Livewire.first().swap(dropped_id);
            ">
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        @endif
      </table>
    </main>
  </div>
</div>
