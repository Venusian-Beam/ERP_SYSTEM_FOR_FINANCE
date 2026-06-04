<script setup>
import MoneyDisplay from './MoneyDisplay.vue'

defineProps({
  transaction: { type: Object, required: true },
})
</script>

<template>
  <tr>
    <td>{{ transaction.date }}</td>
    <td>{{ transaction.reference }}</td>
    <td>{{ transaction.account }}</td>
    <td class="text-end"><MoneyDisplay :amount="transaction.debit || 0" /></td>
    <td class="text-end"><MoneyDisplay :amount="transaction.credit || 0" /></td>
  </tr>
</template>
