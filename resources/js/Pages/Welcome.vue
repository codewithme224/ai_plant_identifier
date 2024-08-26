<template>
    <div class="flex flex-col min-h-screen bg-gradient-to-br from-green-50 to-green-200">
      <NavBar />
      <main class="flex-grow py-12">
        <div class="container px-4 mx-auto">
          <header class="mb-12 text-center">
            <h1 class="mb-4 text-5xl font-bold text-green-800">Plant Identifier</h1>
            <p class="text-xl text-gray-700">Uncover the secrets of nature with our AI-powered plant recognition tool</p>
          </header>

          <div class="flex flex-wrap -mx-4">
            <!-- Left side: Upload/Capture and Image Preview -->
            <div class="w-full px-4 mb-8 md:w-1/2">
              <div class="p-8 bg-white shadow-xl rounded-2xl">
                <h2 class="mb-6 text-2xl font-semibold text-green-700">Upload or Capture Plant Image</h2>
                <div class="flex justify-center mb-6 space-x-4">
                  <label for="dropzone-file" class="flex flex-col items-center justify-center w-1/2 h-40 transition duration-300 border-2 border-green-300 border-dashed cursor-pointer rounded-xl bg-green-50 hover:bg-green-100">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                      <svg class="w-10 h-10 mb-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                      </svg>
                      <p class="mb-2 text-sm text-gray-600"><span class="font-semibold">Upload Image</span></p>
                    </div>
                    <input id="dropzone-file" type="file" class="hidden" @change="handleFileUpload" accept="image/*" />
                  </label>

                  <button @click="startCamera" class="flex flex-col items-center justify-center w-1/2 h-40 transition duration-300 border-2 border-green-300 border-dashed cursor-pointer rounded-xl bg-green-50 hover:bg-green-100">
                    <svg class="w-10 h-10 mb-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <p class="mb-2 text-sm text-gray-600"><span class="font-semibold">Capture Image</span></p>
                  </button>
                </div>

                <div v-if="showCamera" class="mb-6">
                  <video ref="video" class="w-full h-64 rounded-xl" autoplay></video>
                  <button @click="captureImage" class="w-full px-6 py-3 mt-4 text-lg font-bold text-white transition duration-300 bg-green-600 rounded-xl hover:bg-green-700">
                    Capture Image
                  </button>
                </div>

                <div v-if="imagePreview" class="mb-6">
                  <img :src="imagePreview" alt="Plant image" class="object-cover w-full h-64 shadow-md rounded-xl">
                </div>

                <button
                  v-if="imagePreview"
                  @click="identifyPlant"
                  :disabled="loading"
                  class="w-full px-6 py-3 text-lg font-bold text-white transition duration-300 bg-green-600 rounded-xl hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {{ loading ? 'Identifying...' : 'Identify Plant' }}
                </button>

                <div v-if="error" class="p-4 mt-4 text-red-700 bg-red-100 rounded-xl">
                  {{ error }}
                </div>
              </div>
            </div>

            <!-- Right side: Plant Information -->
            <div v-if="plantInfo" class="w-full px-4 md:w-1/2">
              <div class="p-8 bg-white shadow-xl rounded-2xl">
                <h5 class="mb-6 text-3xl font-bold text-green-700">{{ plantInfo.name }}</h5>

                <div class="mb-8">
                  <h3 class="mb-4 text-2xl font-semibold text-green-600">Scientific Details</h3>
                  <table class="w-full border-collapse">
                    <tr>
                      <td class="py-3 pr-4 font-medium border-b border-green-100">Scientific Name:</td>
                      <td class="py-3 italic border-b border-green-100">{{ plantInfo.scientificName }}</td>
                    </tr>
                    <tr>
                      <td class="py-3 pr-4 font-medium border-b border-green-100">Family:</td>
                      <td class="py-3 border-b border-green-100">{{ plantInfo.family }}</td>
                    </tr>
                  </table>
                </div>

                <div class="mb-8">
                  <h3 class="mb-4 text-2xl font-semibold text-green-600">Description</h3>
                  <p class="leading-relaxed text-gray-700">{{ plantInfo.descript }}</p>
                </div>

                <div class="mb-8">
                  <h3 class="mb-4 text-2xl font-semibold text-green-600">Plant Health</h3>
                  <p class="leading-relaxed text-gray-700">{{ plantInfo.plantHealth }}</p>
                </div>

                <div class="mb-8">
                  <h3 class="mb-4 text-2xl font-semibold text-green-600">Care Instructions</h3>

                  <div class="mb-4">
                    <h4 class="mb-2 text-lg font-medium text-green-500">Sunlight</h4>
                    <p class="text-gray-700">{{ plantInfo.sunlight }}</p>
                  </div>

                  <div class="mb-4">
                    <h4 class="mb-2 text-lg font-medium text-green-500">Watering</h4>
                    <p class="text-gray-700">{{ plantInfo.watering }}</p>
                  </div>
                </div>

                <div v-if="plantInfo.additionalInfo" class="p-6 bg-yellow-100 rounded-xl">
                  <p class="font-semibold text-yellow-800">Note: {{ plantInfo.additionalInfo }}</p>
                </div>

                <!-- New Plant Health Assessment Section -->
                <div class="mb-8">
                    <h3 class="mb-4 text-2xl font-semibold text-green-600">Plant Health Assessment</h3>
                    <div class="p-4 bg-gray-100 rounded-xl">
                    <div class="flex items-center mb-2">
                        <div class="w-1/3 text-gray-700">Overall Health:</div>
                        <div class="flex items-center w-2/3">
                        <div class="w-full bg-gray-300 rounded-full h-2.5 mr-2">
                            <div class="bg-green-600 h-2.5 rounded-full" :style="{ width: `${plantHealth.overall}%` }"></div>
                        </div>
                        <span class="text-sm font-medium text-gray-700">{{ plantHealth.overall }}%</span>
                        </div>
                    </div>
                    <div v-for="(value, key) in plantHealth.aspects" :key="key" class="flex items-center mb-2">
                        <div class="w-1/3 text-gray-700 capitalize">{{ key }}:</div>
                        <div class="flex items-center w-2/3">
                        <div class="w-full bg-gray-300 rounded-full h-2.5 mr-2">
                            <div class="bg-blue-600 h-2.5 rounded-full" :style="{ width: `${value}%` }"></div>
                        </div>
                        <span class="text-sm font-medium text-gray-700">{{ value }}%</span>
                        </div>
                    </div>
                    </div>
                    <p class="mt-4 text-gray-700">{{ plantHealth.advice }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- How to use section -->
          <div class="mt-16">
            <h2 class="mb-8 text-3xl font-bold text-center text-green-800">How to Use Plant Identifier</h2>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
              <div class="p-6 bg-gray-800 shadow-md rounded-xl">
                <div class="flex items-center justify-center w-12 h-12 mb-4 bg-green-500 rounded-full">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  </svg>
                </div>
                <h3 class="mb-2 text-xl font-semibold text-green-400">Upload Image</h3>
                <p class="text-gray-300">Take a clear photo of the plant you want to identify and upload it to our app.</p>
              </div>
              <div class="p-6 bg-gray-800 shadow-md rounded-xl">
                <div class="flex items-center justify-center w-12 h-12 mb-4 bg-green-500 rounded-full">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                  </svg>
                </div>
                <h3 class="mb-2 text-xl font-semibold text-green-400">AI Analysis</h3>
                <p class="text-gray-300">Our AI will analyze the image and identify the plant species with high accuracy.</p>
              </div>
              <div class="p-6 bg-gray-800 shadow-md rounded-xl">
                <div class="flex items-center justify-center w-12 h-12 mb-4 bg-green-500 rounded-full">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
                <h3 class="mb-2 text-xl font-semibold text-green-400">Get Information</h3>
                <p class="text-gray-300">Receive detailed information about the plant, including care instructions and interesting facts.</p>
              </div>
            </div>
          </div>
        </div>
      </main>
      <Footer />
    </div>
  </template>

  <script setup>
  import { ref } from 'vue'
  import { usePlantIdentifier } from '@/Composables/usePlantIdentifier'
  import NavBar from '@/Components/NavBar.vue'
  import Footer from '@/Components/Footer.vue'

  const {
  file,
  imagePreview,
  loading,
  error,
  plantInfo,
  setFile,
  identifyPlant,
} = usePlantIdentifier()

const video = ref(null)
const showCamera = ref(false)

const handleFileUpload = (event) => {
  if (event.target.files) {
    setFile(event.target.files[0])
    showCamera.value = false
  }
}

const startCamera = async () => {
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ video: true })
    if (video.value) {
      video.value.srcObject = stream
    }
    showCamera.value = true
  } catch (err) {
    console.error("Error accessing the camera:", err)
    error.value = "Unable to access the camera. Please check your permissions."
  }
}

const captureImage = () => {
  const canvas = document.createElement('canvas')
  canvas.width = video.value.videoWidth
  canvas.height = video.value.videoHeight
  canvas.getContext('2d').drawImage(video.value, 0, 0)
  canvas.toBlob((blob) => {
    setFile(new File([blob], "captured_image.jpg", { type: "image/jpeg" }))
  }, 'image/jpeg')
  showCamera.value = false
  stopCamera()
}

const stopCamera = () => {
  if (video.value && video.value.srcObject) {
    video.value.srcObject.getTracks().forEach(track => track.stop())
  }
}

const plantHealth = ref({
  overall: 0,
  aspects: {
    foliage: 0,
    stem: 0,
    roots: 0,
  },
  advice: '',
})

const assessPlantHealth = () => {
  // This is a mock function. In a real app, this would be based on AI analysis of the image.
  plantHealth.value = {
    overall: Math.floor(Math.random() * 30) + 70, // Random value between 70 and 100
    aspects: {
      foliage: Math.floor(Math.random() * 40) + 60,
      stem: Math.floor(Math.random() * 40) + 60,
      roots: Math.floor(Math.random() * 40) + 60,
    },
    advice: "Based on our analysis, your plant appears to be in good health. To maintain its vitality, ensure it receives adequate sunlight and water according to its specific needs. Regular pruning and fertilization can further enhance its growth."
  }
}

const handleIdentifyPlant = async () => {
  try {
    await identifyPlant()
    if (plantInfo.value) {
      assessPlantHealth()
    }
  } catch (e) {
    console.error('Plant identification error:', e.message || 'An unknown error occurred')
  }
}
  </script>
