<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;

new #[Layout('components.layouts.frontend')] class extends Component {
    public $success = null; // For flash messages
    public $todos = []; // Original todos
    public ?int $id = null; // Track the ID being edited (null for new todos)
    public string $searchQuery = ''; // Property to hold the search query

    #[Validate('required|string|max:255')]
    public string $title = '';
    #[Validate('required|string|max:1000')]
    public string $content = '';

    public function mount()
    {
        $this->todos = [];
        $this->id = null;
        $this->title = '';
        $this->content = '';
    }

    public function resetTodos()
    {
        $this->todos = [];
        $this->resetForm();
        session()->flash('success', 'All todos have been reset!');
    }

    private function resetForm()
    {
        $this->id = null;
        $this->title = '';
        $this->content = '';
    }

    public function saveTodo()
    {
        $this->validate();
        if ($this->id !== null) {
            foreach ($this->todos as &$todo) {
                if ($todo['id'] === $this->id) {
                    $todo['title'] = $this->title;
                    $todo['content'] = $this->content;
                    session()->flash('success', 'Todo updated successfully!');
                    break;
                }
            }
        } else {
            $newId = count($this->todos) > 0 ? max(array_column($this->todos, 'id')) + 1 : 1;
            $this->todos[] = [
                'id' => $newId,
                'title' => $this->title,
                'content' => $this->content,
            ];
            session()->flash('success', 'Todo saved successfully!');
        }
        $this->resetForm();
    }

    // FIX 1: Use ID instead of index
    public function editTodo($id)
    {
        foreach ($this->todos as $todo) {
            if ($todo['id'] === $id) {
                $this->id = $id;
                $this->title = $todo['title'];
                $this->content = $todo['content'];
                break;
            }
        }
    }

    // FIX 1: Use ID instead of index
    public function deleteTodo($id)
    {
        $this->todos = array_filter($this->todos, fn($todo) => $todo['id'] !== $id);
        session()->flash('success', 'Todo deleted successfully!');
    }

    // FIX 2: Improved case-insensitive search
    public function getFilteredTodosProperty()
    {
        if (empty($this->searchQuery)) {
            return $this->todos;
        }
        $query = mb_strtolower($this->searchQuery, 'UTF-8');

        return array_filter($this->todos, function ($todo) use ($query) {
            return mb_strpos(mb_strtolower($todo['title'], 'UTF-8'), $query) !== false || mb_strpos(mb_strtolower($todo['content'], 'UTF-8'), $query) !== false;
        });
    }
};
?>
<div>
    <div class="container mx-auto px-4 py-8">
        <flux:heading size="xl" class="text-center">{{ __('Welcome to the Todo App') }}</flux:heading>

        <div
            class="w-full max-w-xl my-10 mx-auto p-4 bg-white border border-gray-200 rounded-lg shadow-sm sm:p-6 md:p-8 dark:bg-gray-800 dark:border-gray-700">
            <form class="space-y-6" wire:submit.prevent="saveTodo">
                <flux:text class="text-center" size="xl">{{ __('Create or update your todo!') }}</flux:text>
                <flux:input wire:model="title" :label="__('Title')" type="text" placeholder="Title" />
                <flux:textarea wire:model="content" :label="__('Content')" placeholder="Content" rows="4" />
                <flux:button type="submit" variant="primary" class="w-full">
                    {{ __('Save') }}
                </flux:button>
            </form>
        </div>

        @if (session()->has('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg flex items-center justify-between">
                <flux:text class="font-medium">{{ session('success') }}</flux:text>
                <flux:button wire:click="$set('success', null)" icon="x-mark" variant="ghost"
                    class="text-green-500 hover:text-green-700">
                </flux:button>
            </div>
        @endif

        <div class="flex items-center justify-between mb-4 gap-5">
            <flux:heading size="lg">{{ __('Your Todos') }}</flux:heading>
            <flux:input wire:model.live.debounce.150ms="searchQuery" placeholder="Search todos" clearable
                class="max-w-lg" />
            <flux:button wire:click="resetTodos" variant="danger"
                wire:confirm="Are you sure you want to reset all todos?">
                {{ __('Reset Todos') }}
            </flux:button>
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
                    @forelse ($this->filteredTodos as $todo)
                        <tr wire:loading.remove wire:target="searchQuery" wire:key="todo-{{ $todo['id'] }}"
                            class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4">{{ $todo['title'] }}</td>
                            <td class="px-6 py-4">{{ $todo['content'] }}</td>
                            <td class="px-6 py-4 flex space-x-2">
                                <flux:button wire:click="editTodo({{ $todo['id'] }})">Edit</flux:button>
                                <flux:button wire:click="deleteTodo({{ $todo['id'] }})" variant="danger"
                                    wire:confirm="Are you sure you want to delete this todo?">Delete
                                </flux:button>
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
