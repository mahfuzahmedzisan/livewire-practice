<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Computed;
use App\Models\Todo;

new #[Layout('components.layouts.frontend')] class extends Component {
    // public ?Todo $todo;

    // public string $title = '';
    // public string $content = '';
    public string $searchQuery = '';

    #[Computed]
    public function todos()
    {
        return Todo::query()
            ->when($this->searchQuery, function ($query) {
                $query->where('title', 'like', '%' . $this->searchQuery . '%')->orWhere('content', 'like', '%' . $this->searchQuery . '%');
            })
            ->get();
    }

    // public function mount()
    // {
    //     $this->todos = $this->todoss();
    // }
}; ?>

<div>
    <div class="container mx-auto px-4 py-8">
        <flux:heading size="xl" class="text-center">{{ __('Welcome to the Todo App') }}</flux:heading>

        {{-- <div
            class="w-full max-w-xl my-10 mx-auto p-4 bg-white border border-gray-200 rounded-lg shadow-sm sm:p-6 md:p-8 dark:bg-gray-800 dark:border-gray-700">
            <form class="space-y-6" wire:submit.prevent="saveTodo">
                <flux:text class="text-center" size="xl">{{ __('Create or update your todo!') }}</flux:text>
                <flux:input wire:model="title" :label="__('Title')" type="text" placeholder="Title" />
                <flux:textarea wire:model="content" :label="__('Content')" placeholder="Content" rows="4" />
                <flux:button type="submit" variant="primary" class="w-full">
                    {{ __('Save') }}
                </flux:button>
            </form>
        </div> --}}

        @if (session()->has('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg flex items-center justify-between">
                <flux:text class="font-medium">{{ session('success') }}</flux:text>
                <flux:button wire:click="$set('success', null)" icon="x-mark" variant="ghost"
                    class="text-green-500 hover:text-green-700">
                </flux:button>
            </div>
        @endif

        <div class="flex items-center justify-between mb-4 gap-5 my-5">
            <flux:heading size="lg">{{ __('Your Todos') }}</flux:heading>
            <flux:input wire:model.live.debounce.150ms="searchQuery" placeholder="Search todos" clearable
                class="max-w-lg" />
            {{-- <flux:button wire:click="resetTodos" variant="danger"
                wire:confirm="Are you sure you want to reset all todos?">
                {{ __('Reset Todos') }}
            </flux:button> --}}
        </div>

        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th width="5%" class="px-6 py-3">#</th>
                        <th class="px-6 py-3">Title</th>
                        <th class="px-6 py-3">Content</th>
                        <th width="15%" class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>

                    <tr>
                        <td class="text-center" colspan="4">
                            <div wire:dirty wire:target="searchQuery">
                                <flux:text class="py-4 font-medium text-center flex items-center justify-center gap-5">
                                    <flux:icon.loading />{{ __('Searching...') }}
                                </flux:text>
                            </div>
                        </td>
                    </tr>
                    @forelse ($this->todos() as $todo)
                        <tr wire:loading.remove wire:target="searchQuery" wire:key="todo-{{ $todo['id'] }}"
                            class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4">{{ $todo->title }}</td>
                            <td class="px-6 py-4">{{ $todo->content }}</td>
                            <td class="px-6 py-4 flex space-x-2">
                                {{-- <flux:button wire:click="editTodo({{ $todo->id }})">Edit</flux:button>
                                <flux:button wire:click="deleteTodo({{ $todo->id }})" variant="danger"
                                    wire:confirm="Are you sure you want to delete this todo?">Delete
                                </flux:button> --}}
                            </td>
                        </tr>
                    @empty
                        <tr wire:loading.remove wire:target="searchQuery">
                            <td colspan="4">
                                <flux:text class="font-medium px-6 py-4 text-center">{{ __('No todos found.') }}
                                </flux:text>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
