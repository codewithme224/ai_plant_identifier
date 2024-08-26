<template>
    <nav class="bg-green-600 shadow-lg">
      <div class="max-w-6xl px-4 mx-auto">
        <div class="flex justify-between">
          <div class="flex space-x-7">
            <div>
              <a href="#" class="flex items-center px-2 py-4">
                <span class="text-lg font-semibold text-white">PlantID</span>
              </a>
            </div>
            <div class="items-center hidden space-x-1 md:flex">
              <a href="#" class="px-2 py-4 text-white transition duration-300 hover:text-green-200">Home</a>
              <a href="#" class="px-2 py-4 text-white transition duration-300 hover:text-green-200">About</a>
              <a href="#" class="px-2 py-4 text-white transition duration-300 hover:text-green-200">Services</a>
              <a href="#" class="px-2 py-4 text-white transition duration-300 hover:text-green-200">Contact</a>
            </div>
          </div>
          <!-- Mobile menu button -->
          <div class="flex items-center md:hidden">
            <button class="outline-none mobile-menu-button">
              <svg class="w-6 h-6 text-white hover:text-green-200"
                x-show="!showMenu"
                fill="none"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path d="M4 6h16M4 12h16M4 18h16"></path>
              </svg>
            </button>
          </div>
        </div>
      </div>
      <!-- mobile menu -->
      <div class="hidden mobile-menu">
        <ul class="">
          <li><a href="#" class="block px-2 py-4 text-sm text-white transition duration-300 hover:bg-green-500">Home</a></li>
          <li><a href="#" class="block px-2 py-4 text-sm text-white transition duration-300 hover:bg-green-500">About</a></li>
          <li><a href="#" class="block px-2 py-4 text-sm text-white transition duration-300 hover:bg-green-500">Services</a></li>
          <li><a href="#" class="block px-2 py-4 text-sm text-white transition duration-300 hover:bg-green-500">Contact</a></li>
        </ul>
      </div>
    </nav>
  </template>

  <script setup>
  import { onMounted } from 'vue'

  onMounted(() => {
    const btn = document.querySelector("button.mobile-menu-button");
    const menu = document.querySelector(".mobile-menu");

    btn.addEventListener("click", () => {
      menu.classList.toggle("hidden");
    });
  })
  </script>
