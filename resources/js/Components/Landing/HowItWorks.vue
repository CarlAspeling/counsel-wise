<script setup>
const steps = [
    {
        number: 1,
        title: 'Input Client Information',
        description: 'Input your client\'s therapeutically-relevant information, the system processes this information and weighs it against the database of therapeutic modalities, and be provided with client-specific therapeutic recommendations'
    },
    {
        number: 2,
        title: 'System Processes Information Against Database',
        description: 'Input your client\'s therapeutically-relevant information, the system processes this information and weighs it against the database of therapeutic modalities, and be provided with client-specific therapeutic recommendations'
    },
    {
        number: 3,
        title: 'Client-Specific Recommendations Outputted',
        description: 'Input your client\'s therapeutically-relevant information, the system processes this information and weighs it against the database of therapeutic modalities, and be provided with client-specific therapeutic recommendations'
    }
]
</script>

<template>
    <div class="bg-teal-500 rounded-2xl p-8 lg:p-12 text-white">
        <h2 class="text-3xl lg:text-4xl font-bold text-center mb-12">
            How It Works
        </h2>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-12">
            <div
                v-for="step in steps"
                :key="step.number"
                class="text-center"
            >
                <!-- Step Circle -->
                <div class="flex justify-center mb-6">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center">
                        <span class="text-2xl font-bold text-teal-500">{{ step.number }}</span>
                    </div>
                </div>

                <!-- Step Title -->
                <h3 class="text-lg font-semibold mb-4 text-center">
                    {{ step.title }}
                </h3>
            </div>
        </div>

        <!-- Description Text -->
        <div class="mt-8 text-center">
            <p class="text-white/90 leading-relaxed max-w-4xl mx-auto">
                {{ steps[0].description }}
            </p>
        </div>
    </div>
</template>