<script setup>
import StatusBadge from '@/components/ui/StatusBadge.vue'
defineProps({ status: String, size: String })
</script>

<template><StatusBadge :status="status" /></template>
