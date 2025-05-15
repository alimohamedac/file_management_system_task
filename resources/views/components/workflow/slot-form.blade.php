@props([
    'slot',
    'index',
    'users',
    'parentSlots' => [],
])

<div class="bg-gray-50 p-4 rounded-lg mb-4">
    @if(isset($slot['id']))
        <input type="hidden" :name="'slots['+index+'][id]'" :value="slot.id">
    @endif
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div>
            <x-form.input
                :name="'slots['+index+'][slot_number]'"
                label="Slot Number"
                x-model="slot.slot_number"
                required
                placeholder="e.g., 1.0"
            />
        </div>
        <div>
            <x-form.select
                :name="'slots['+index+'][approval_method]'"
                label="Approval Method"
                x-model="slot.approval_method"
                required
                :options="[
                    'single' => 'Single Approval',
                    'multi' => 'Multiple Approvals'
                ]"
            />
        </div>
    </div>

    <div class="mb-4">
        <x-form.textarea
            :name="'slots['+index+'][description]'"
            label="Description"
            x-model="slot.description"
            required
            rows="2"
        />
    </div>

    <div class="mb-4">
        <x-form.select
            :name="'slots['+index+'][parent_slot_id]'"
            label="Parent Slot (Optional)"
            x-model="slot.parent_slot_id"
            placeholder="No Parent"
        >
            <template x-for="(otherSlot, otherIndex) in slots.slice(0, index)" :key="otherSlot.id || otherIndex">
                <template x-if="otherSlot.id !== slot.id">
                    <option :value="otherSlot.id || otherIndex" x-text="'Slot ' + otherSlot.slot_number"></option>
                </template>
            </template>
        </x-form.select>
    </div>

    <div class="mb-4">
        <x-form.select
            :name="'slots['+index+'][user_ids][]'"
            label="Assigned Users"
            x-model="slot.user_ids"
            :options="$users->pluck('name', 'id')"
            multiple
            required
        />
    </div>

    <div class="flex justify-end">
        <x-button
            type="button"
            variant="danger"
            @click="removeSlot(index)"
        >
            Remove Slot
        </x-button>
    </div>
</div>
