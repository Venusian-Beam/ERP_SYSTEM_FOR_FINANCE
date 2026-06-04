<script setup>
defineProps({
  columns: { type: Array, default: () => [] },
  rows: { type: Array, default: () => [] },
  emptyMessage: { type: String, default: 'No records found.' },
})
</script>

<template>
  <div class="table-responsive finance-table">
    <table class="table whitespace-nowrap min-w-full">
      <thead>
        <tr>
          <th v-for="column in columns" :key="column.key">
            {{ column.label }}
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="!rows.length">
          <td :colspan="columns.length || 1" class="text-center text-textmuted py-6">
            {{ emptyMessage }}
          </td>
        </tr>
        <tr v-for="(row, index) in rows" :key="row.id || index">
          <td v-for="column in columns" :key="column.key">
            <slot :name="column.key" :row="row" :value="row[column.key]">
              {{ row[column.key] }}
            </slot>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
