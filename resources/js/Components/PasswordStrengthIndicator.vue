<template>
    <div class="mt-2">
        <!-- Strength Progress Bar -->
        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
            <div 
                class="h-2 rounded-full transition-all duration-300 ease-in-out"
                :class="strengthBarColor"
                :style="`width: ${strength}%`"
            ></div>
        </div>

        <!-- Strength Text -->
        <div class="flex justify-between items-center mt-1">
            <span class="text-xs font-medium" :class="strengthTextColor">
                {{ strengthLevel }}
            </span>
            <span class="text-xs text-gray-500 dark:text-gray-400">
                {{ strength }}/100
            </span>
        </div>

        <!-- Password Requirements Checklist -->
        <div v-if="showRequirements" class="mt-3 space-y-1">
            <div class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">
                Password Requirements:
            </div>
            <div 
                v-for="(requirement, index) in requirements" 
                :key="index"
                class="flex items-center text-xs"
            >
                <svg 
                    class="w-3 h-3 mr-2 flex-shrink-0" 
                    :class="getRequirementIconClass(requirement)"
                    fill="currentColor" 
                    viewBox="0 0 20 20"
                >
                    <path 
                        v-if="requirement.met"
                        fill-rule="evenodd" 
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" 
                        clip-rule="evenodd"
                    />
                    <circle 
                        v-else-if="!requirement.serverValidated" 
                        cx="10" cy="10" r="8" 
                        stroke="currentColor" 
                        stroke-width="2" 
                        fill="none"
                    />
                    <!-- Clock icon for server-validated requirements -->
                    <path 
                        v-else
                        fill-rule="evenodd" 
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" 
                        clip-rule="evenodd"
                    />
                </svg>
                <span 
                    :class="getRequirementTextClass(requirement)"
                    class="transition-colors duration-200"
                >
                    {{ requirement.text }}
                    <span v-if="requirement.serverValidated && !requirement.met" class="text-xs text-gray-400 ml-1">
                        (verified on submit)
                    </span>
                </span>
            </div>
        </div>

        <!-- Validation Errors -->
        <div v-if="errors.length > 0 && showErrors" class="mt-2 space-y-1">
            <div 
                v-for="(error, index) in errors" 
                :key="index"
                class="text-xs text-red-600 dark:text-red-400"
            >
                {{ error }}
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { 
    validatePassword, 
    getPasswordStrengthLevel, 
    getPasswordStrengthColor,
    getPasswordRequirements
} from '@/utils/passwordValidation.js';

const props = defineProps({
    password: {
        type: String,
        default: ''
    },
    showRequirements: {
        type: Boolean,
        default: true
    },
    showErrors: {
        type: Boolean,
        default: false
    }
});

const validation = computed(() => validatePassword(props.password));
const strength = computed(() => validation.value.strength);
const strengthLevel = computed(() => getPasswordStrengthLevel(strength.value));
const errors = computed(() => validation.value.errors);

const strengthBarColor = computed(() => {
    if (strength.value < 30) return 'bg-red-500';
    if (strength.value < 50) return 'bg-orange-500';
    if (strength.value < 70) return 'bg-yellow-500';
    if (strength.value < 90) return 'bg-blue-500';
    return 'bg-green-500';
});

const strengthTextColor = computed(() => {
    if (strength.value < 30) return 'text-red-600 dark:text-red-400';
    if (strength.value < 50) return 'text-orange-600 dark:text-orange-400';
    if (strength.value < 70) return 'text-yellow-600 dark:text-yellow-400';
    if (strength.value < 90) return 'text-blue-600 dark:text-blue-400';
    return 'text-green-600 dark:text-green-400';
});

const requirements = computed(() => {
    const reqs = getPasswordRequirements();
    return reqs.map(req => ({
        ...req,
        met: req.test(props.password)
    }));
});

// Helper methods for requirement styling
const getRequirementIconClass = (requirement) => {
    if (requirement.met) {
        return 'text-green-500';
    } else if (requirement.serverValidated) {
        return 'text-blue-500'; // Clock icon for server validation
    } else {
        return 'text-gray-400';
    }
};

const getRequirementTextClass = (requirement) => {
    if (requirement.met) {
        return 'text-green-600 dark:text-green-400';
    } else if (requirement.serverValidated) {
        return 'text-blue-600 dark:text-blue-400';
    } else {
        return 'text-gray-500 dark:text-gray-400';
    }
};
</script>