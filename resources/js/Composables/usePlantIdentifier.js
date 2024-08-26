import { ref, computed } from 'vue'

export function usePlantIdentifier() {
    const file = ref(null)
    const imagePreview = ref(null)
    const loading = ref(false)
    const error = ref(null)
    const plantInfo = ref(null)

    const canIdentify = computed(() => file.value !== null)

    const setFile = (newFile) => {
      file.value = newFile
      error.value = null
      plantInfo.value = null
      if (newFile) {
        const reader = new FileReader()
        reader.onload = e => {
          imagePreview.value = e.target.result
        }
        reader.readAsDataURL(newFile)
      } else {
        imagePreview.value = null
      }
    }

  const identifyPlant = async () => {
    if (!file.value) {
      error.value = 'Please select an image first.'
      return
    }

    loading.value = true
    error.value = null
    plantInfo.value = null

    try {
      const formData = new FormData()
      formData.append('image', file.value)

      const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content')

      const response = await fetch('/api/identify-plant', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken
        },
        body: formData,
      })


      if (!response.ok) {
        throw new Error('Failed to identify plant')
      }

      const result = await response.json()
      console.log('API Response:', result) // Log the full response
      plantInfo.value = parseResponse(result)
    } catch (e) {
      console.error('Error identifying plant:', e)
      error.value = 'An error occurred while identifying the plant. Please try again.'
    } finally {
      loading.value = false
    }
  }

  const parseResponse = (response) => {
    // Safely access the fields and remove any Markdown formatting
    const stripMarkdown = (text) => text.replace(/\*\*/g, '').trim();

    return {
        name: stripMarkdown(response.name),
        scientificName: stripMarkdown(response.scientificName || 'Not provided'),
        family: stripMarkdown(response.family || 'Not provided'),
        descript: stripMarkdown(response.descript || 'No description available'),
        sunlight: stripMarkdown(response.sunlightNeeds || 'Not provided'),
        watering: stripMarkdown(response.wateringNeeds || 'Not provided'),
        careInstructions: {
            other: stripMarkdown(response.careInstructions || 'Not provided')
        },
        plantHealth: stripMarkdown(response.plantHealth || 'No health information provided'),
        additionalInfo: stripMarkdown(response.additionalInfo || 'No additional information provided'),
    };
};


  return {
    file,
    imagePreview,
    loading,
    error,
    plantInfo,
    canIdentify,
    setFile,
    identifyPlant,
  }
}
