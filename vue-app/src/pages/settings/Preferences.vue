<script setup>
import { ref, onMounted } from 'vue'
import apiClient from '@/utils/apiClient'
import SettingsWorkspace from '@/components/finance/SettingsWorkspace.vue'

const sections = ref([])

onMounted(async () => {
  try {
    const { data } = await apiClient.get('/settings/preferences')
    sections.value = data.sections || data.records || data
  } catch (e) {
    console.error('Failed to load preferences:', e)
  }
})

const handleSave = async () => {
  try {
    await apiClient.put('/settings/preferences', { sections: sections.value })
    console.log('Preferences saved')
  } catch (e) {
    console.error('Failed to save preferences:', e)
  }
}
</script>
<template><SettingsWorkspace title="Preferences" subtitle="Personalize themes, notifications, and dashboard defaults" :sections="sections" @save="handleSave" /></template>
