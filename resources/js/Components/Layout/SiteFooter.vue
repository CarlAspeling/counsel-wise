<script setup>
import { Link } from '@inertiajs/vue3'
import { MapPinIcon, PhoneIcon, EnvelopeIcon } from '@heroicons/vue/24/outline'
</script>

<template>
    <footer class="bg-secondary-900 text-white px-6 py-4">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Pages -->
            <div>
                <h3 class="font-bold mb-4">Pages</h3>
                <ul class="space-y-2">
                    <li><Link href="#" class="nav-link">About Us</Link></li>
                    <li><Link href="#" class="nav-link">Services</Link></li>
                    <li><Link href="#" class="nav-link">Blog</Link></li>
                </ul>
            </div>

            <!-- Contact Us -->
            <div>
                <h3 class="font-bold mb-4">Contact Us</h3>
                <ul class="space-y-2 text-white">
                    <li class="flex items-center">
                        <MapPinIcon class="h-4 w-4 text-primary-300 mr-3" />
                        George, Western Cape
                    </li>
                    <li class="flex items-center">
                        <PhoneIcon class="h-4 w-4 text-primary-300 mr-3" />
                        082 394 3462
                    </li>
                    <li class="flex items-center">
                        <EnvelopeIcon class="h-4 w-4 text-primary-300 mr-3" />
                        info@counselwise.co.za
                    </li>
                </ul>
            </div>

            <!-- Legal -->
            <div>
                <h3 class="font-bold mb-4">Legal</h3>
                <ul class="space-y-2">
                    <li><Link href="#" class="nav-link">Privacy Policy</Link></li>
                    <li><Link href="#" class="nav-link">Terms of Service</Link></li>
                    <li><Link href="#" class="nav-link">Cookie Policy</Link></li>
                </ul>
            </div>

            <!-- Socials -->
            <div>
                <h3 class="font-bold mb-4">Socials</h3>
                <div class="flex space-x-4">
                    <Link href="#" class="w-10 h-10 bg-secondary-700 rounded-full flex items-center justify-center hover:bg-primary-500 transition-colors">
                        <span class="sr-only">Facebook</span>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </Link>
                    <Link href="#" class="w-10 h-10 bg-secondary-700 rounded-full flex items-center justify-center hover:bg-primary-500 transition-colors">
                        <span class="sr-only">Twitter</span>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                        </svg>
                    </Link>
                    <Link href="#" class="w-10 h-10 bg-secondary-700 rounded-full flex items-center justify-center hover:bg-primary-500 transition-colors">
                        <span class="sr-only">LinkedIn</span>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                        </svg>
                    </Link>
                    <Link href="#" class="w-10 h-10 bg-secondary-700 rounded-full flex items-center justify-center hover:bg-primary-500 transition-colors">
                        <span class="sr-only">Instagram</span>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 6.62 5.367 11.987 11.988 11.987 6.62 0 11.987-5.367 11.987-11.987C24.014 5.367 18.637.001 12.017.001zM8.449 16.988c-1.297 0-2.348-1.051-2.348-2.348 0-1.297 1.051-2.348 2.348-2.348 1.297 0 2.348 1.051 2.348 2.348 0 1.297-1.051 2.348-2.348 2.348zm7.718 0c-1.297 0-2.348-1.051-2.348-2.348 0-1.297 1.051-2.348 2.348-2.348 1.297 0 2.348 1.051 2.348 2.348 0 1.297-1.051 2.348-2.348 2.348z"/>
                        </svg>
                    </Link>
                </div>
            </div>
        </div>
    </footer>
</template>