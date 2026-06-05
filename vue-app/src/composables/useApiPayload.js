import { onMounted, ref } from 'vue'

export function useApiPayload(loader, defaults = {}) {
  const metrics = ref(defaults.metrics || [])
  const records = ref(defaults.records || [])
  const sections = ref(defaults.sections || [])
  const loading = ref(true)
  const error = ref(null)
  const currentPage = ref(1)
  const perPage = ref(defaults.perPage || 25)
  const totalRecords = ref(0)
  const totalPages = ref(0)

  const fetchData = async (page = 1) => {
    loading.value = true
    error.value = null
    try {
      const data = await loader({ params: { page, per_page: perPage.value } })
      metrics.value = data.metrics || []
      records.value = data.records || data.data || []
      sections.value = data.sections || []
      totalRecords.value = data.total || records.value.length
      totalPages.value = data.last_page || Math.ceil(totalRecords.value / perPage.value)
      currentPage.value = data.current_page || page
    } catch (caught) {
      error.value = caught
      console.error('Failed to fetch page data:', caught)
    } finally {
      loading.value = false
    }
  }

  onMounted(() => fetchData(1))

  const nextPage = () => { if (currentPage.value < totalPages.value) fetchData(currentPage.value + 1) }
  const prevPage = () => { if (currentPage.value > 1) fetchData(currentPage.value - 1) }
  const goToPage = (page) => fetchData(page)

  return {
    metrics, records, sections, loading, error,
    currentPage, perPage, totalRecords, totalPages,
    fetchData, nextPage, prevPage, goToPage,
  }
}
