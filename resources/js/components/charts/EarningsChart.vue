<script setup lang="ts">
import { Line } from 'vue-chartjs';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler,
  ChartOptions,
  ChartData
} from 'chart.js';
import { computed, ref } from 'vue';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler);

interface EarningsData {
  labels: string[];
  daily: number[];
  weekly: number[];
  monthly: number[];
}

interface Props {
  earningsData?: EarningsData;
  height?: number;
}

const props = withDefaults(defineProps<Props>(), {
  height: 300
});

const viewMode = ref<'daily' | 'weekly' | 'monthly'>('daily');

// Mock earnings data for demonstration
const defaultEarningsData: EarningsData = {
  labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
  daily: [120, 150, 180, 200, 250, 300, 280, 320, 380, 420, 450, 500],
  weekly: [840, 1050, 1260, 1400, 1750, 2100, 1960, 2240, 2660, 2940, 3150, 3500],
  monthly: [3600, 4500, 5400, 6000, 7500, 9000, 8400, 9600, 11400, 12600, 13500, 15000]
};

const earningsData = computed(() => props.earningsData || defaultEarningsData);

const chartData = computed<ChartData<'line'>>(() => {
  return {
    labels: earningsData.value.labels,
    datasets: [
      {
        label: `${viewMode.value.charAt(0).toUpperCase() + viewMode.value.slice(1)} Earnings`,
        data: earningsData.value[viewMode.value],
        borderColor: '#3B82F6',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        borderWidth: 3,
        fill: true,
        pointRadius: 4,
        pointHoverRadius: 6,
        pointBackgroundColor: '#3B82F6',
        pointBorderColor: '#ffffff',
        pointBorderWidth: 2,
        tension: 0.4,
      }
    ]
  };
});

const chartOptions = computed<ChartOptions<'line'>>(() => ({
  responsive: true,
  maintainAspectRatio: false,
  interaction: {
    intersect: false,
    mode: 'index' as const,
  },
  plugins: {
    legend: {
      display: false
    },
    tooltip: {
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      titleColor: '#ffffff',
      bodyColor: '#ffffff',
      borderColor: 'rgba(255, 255, 255, 0.1)',
      borderWidth: 1,
      callbacks: {
        label: (context) => {
          return `Earnings: $${context.parsed.y.toLocaleString()}`;
        }
      }
    }
  },
  scales: {
    x: {
      grid: {
        color: 'rgba(0, 0, 0, 0.1)',
        drawBorder: false
      },
      ticks: {
        font: {
          size: 12
        }
      }
    },
    y: {
      beginAtZero: true,
      grid: {
        color: 'rgba(0, 0, 0, 0.1)',
        drawBorder: false
      },
      ticks: {
        font: {
          size: 12
        },
        callback: function(value) {
          return '$' + Number(value).toLocaleString();
        }
      }
    }
  },
  animation: {
    duration: 800,
    easing: 'easeOutQuart'
  }
}));
</script>

<template>
  <div>
    <!-- View Toggle Buttons -->
    <div class="flex gap-2 mb-4">
      <button
        v-for="mode in ['daily', 'weekly', 'monthly']"
        :key="mode"
        @click="viewMode = mode as any"
        :class="{
          'bg-blue-100 text-blue-700 border-blue-300': viewMode === mode,
          'bg-gray-100 text-gray-600 hover:bg-gray-200 border-gray-300': viewMode !== mode
        }"
        class="px-3 py-1 text-xs font-medium rounded-md border transition-colors duration-200 capitalize"
      >
        {{ mode }}
      </button>
    </div>

    <!-- Chart -->
    <div :style="{ height: `${height}px` }">
      <Line :data="chartData" :options="chartOptions" />
    </div>
  </div>
</template>