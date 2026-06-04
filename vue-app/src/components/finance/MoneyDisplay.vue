<script setup>
import { computed } from 'vue'
import { formatCurrency } from '@/utils/formatters'

const props = defineProps({
  amount: { type: Number, default: 0 },
  currency: { type: String, default: 'USD' }
})

const formatted = computed(() => formatCurrency(props.amount, props.currency))
</script>

<template>
  <span class="money" :class="{ negative: props.amount < 0 }">{{ formatted }}</span>
</template>
