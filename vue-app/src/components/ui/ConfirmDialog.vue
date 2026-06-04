<script setup>
defineProps({
  open: { type: Boolean, default: false },
  title: { type: String, default: 'Confirm action' },
  message: { type: String, default: 'Are you sure you want to continue?' },
})

defineEmits(['confirm', 'cancel'])
</script>

<template>
  <div v-if="open" class="fixed inset-0 z-50 grid place-items-center bg-black/40 px-4">
    <section class="box max-w-md w-full">
      <div class="box-body">
        <h2 class="text-lg font-semibold mb-2">{{ title }}</h2>
        <p class="text-textmuted mb-5">{{ message }}</p>
        <div class="flex justify-end gap-2">
          <button class="ti-btn ti-btn-light" type="button" @click="$emit('cancel')">Cancel</button>
          <button class="ti-btn ti-btn-primary" type="button" @click="$emit('confirm')">Confirm</button>
        </div>
      </div>
    </section>
  </div>
</template>
