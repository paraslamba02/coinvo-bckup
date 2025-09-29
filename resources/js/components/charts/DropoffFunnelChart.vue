<script setup lang="ts">
import { Bar } from 'vue-chartjs';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend,
  ChartOptions,
  ChartData
} from 'chart.js';
import { computed } from 'vue';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

interface FunnelStats {
  landing_visitors: number;
  step1_signups: number;
  step2_deposits: number;
  step3_rewards: number;
  total_conversions: number;
  landing_to_step1_rate: number;
  step1_to_step2_rate: number;
  step2_to_step3_rate: number;
  overall_conversion_rate: number;
  dropoff_rates: {
    landing_to_step1: number;
    step1_to_step2: number;
    step2_to_step3: number;
  };
}

interface Props {
  funnelStats: FunnelStats;
  height?: number;
}

const props = withDefaults(defineProps<Props>(), {
  height: 200
});

const chartData = computed<ChartData<'bar'>>(() => {
  const data = [
    props.funnelStats.landing_visitors,
    props.funnelStats.step1_signups,
    props.funnelStats.step2_deposits,
    props.funnelStats.step3_rewards
  ];

  const colors = [
    'rgba(99, 102, 241, 0.8)',   // Landing - Indigo
    'rgba(59, 130, 246, 0.8)',   // Signups - Blue
    'rgba(34, 197, 94, 0.8)',    // Deposits - Green
    'rgba(147, 51, 234, 0.8)'    // Rewards - Purple
  ];

  const borderColors = [
    'rgb(99, 102, 241)',
    'rgb(59, 130, 246)',
    'rgb(34, 197, 94)',
    'rgb(147, 51, 234)'
  ];

  return {
    labels: ['Landing', 'Sign up', 'Deposit', 'Reward'],
    datasets: [
      {
        label: 'Users',
        data: data,
        backgroundColor: colors,
        borderColor: borderColors,
        borderWidth: 2,
        borderRadius: 6,
        borderSkipped: false,
      }
    ]
  };
});

const chartOptions = computed<ChartOptions<'bar'>>(() => ({
  indexAxis: 'y' as const,
  responsive: true,
  maintainAspectRatio: false,
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
        afterLabel: (context) => {
          const index = context.dataIndex;
          const stats = props.funnelStats;

          if (index === 1) {
            return [
              `Conversion: ${stats.landing_to_step1_rate}% from landing`,
              `Drop-off: ${stats.dropoff_rates.landing_to_step1}%`
            ];
          } else if (index === 2) {
            return [
              `Conversion: ${stats.step1_to_step2_rate}% from signups`,
              `Drop-off: ${stats.dropoff_rates.step1_to_step2}%`
            ];
          } else if (index === 3) {
            return [
              `Conversion: ${stats.step2_to_step3_rate}% from deposits`,
              `Drop-off: ${stats.dropoff_rates.step2_to_step3}%`
            ];
          } else if (index === 0) {
            return `Starting point of funnel`;
          }
          return '';
        }
      }
    }
  },
  scales: {
    x: {
      beginAtZero: true,
      grid: {
        color: 'rgba(0, 0, 0, 0.1)'
      },
      ticks: {
        font: {
          size: 11
        }
      }
    },
    y: {
      grid: {
        display: false
      },
      ticks: {
        font: {
          size: 12,
          weight: 'bold'
        }
      }
    }
  },
  animation: {
    duration: 1000,
    easing: 'easeOutQuart'
  }
}));

const funnelSteps = computed(() => [
  {
    label: 'Landing Page',
    value: props.funnelStats.landing_visitors,
    description: 'Visitors who clicked tracking links',
    conversionRate: null,
    dropoffRate: null,
    color: 'text-indigo-600'
  },
  {
    label: 'Sign Up',
    value: props.funnelStats.step1_signups,
    description: 'Users who registered',
    conversionRate: props.funnelStats.landing_to_step1_rate,
    dropoffRate: props.funnelStats.dropoff_rates.landing_to_step1,
    color: 'text-blue-600'
  },
  {
    label: 'Deposit',
    value: props.funnelStats.step2_deposits,
    description: 'Users who made deposits',
    conversionRate: props.funnelStats.step1_to_step2_rate,
    dropoffRate: props.funnelStats.dropoff_rates.step1_to_step2,
    color: 'text-green-600'
  },
  {
    label: 'Reward',
    value: props.funnelStats.step3_rewards,
    description: 'Users who claimed rewards',
    conversionRate: props.funnelStats.step2_to_step3_rate,
    dropoffRate: props.funnelStats.dropoff_rates.step2_to_step3,
    color: 'text-purple-600'
  }
]);
</script>

<template>
  <div class="space-y-6">
    <!-- Chart -->
    <div :style="{ height: `${height}px` }">
      <Bar :data="chartData" :options="chartOptions" />
    </div>

    <!-- Detailed Funnel Steps -->
    <div class="grid gap-3">
      <div
        v-for="(step, index) in funnelSteps"
        :key="step.label"
        class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg"
      >
        <div class="flex items-center space-x-3">
          <div class="flex items-center justify-center w-8 h-8 rounded-full bg-white dark:bg-gray-700 shadow-sm">
            <span class="text-sm font-bold" :class="step.color">{{ index + 1 }}</span>
          </div>
          <div>
            <div class="font-medium text-gray-900 dark:text-gray-100">{{ step.label }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">{{ step.description }}</div>
          </div>
        </div>

        <div class="text-right">
          <div class="font-bold text-lg" :class="step.color">
            {{ step.value.toLocaleString() }}
          </div>
          <div v-if="step.conversionRate !== null" class="text-xs text-gray-600 dark:text-gray-400">
            {{ step.conversionRate }}% conversion
          </div>
          <div v-if="step.dropoffRate !== null" class="text-xs text-red-600">
            {{ step.dropoffRate }}% drop-off
          </div>
        </div>
      </div>
    </div>

    <!-- Overall Stats -->
    <div class="flex justify-between items-center p-4 bg-gradient-to-r from-purple-50 to-blue-50 dark:from-purple-900/20 dark:to-blue-900/20 rounded-lg">
      <div>
        <div class="text-sm text-gray-600 dark:text-gray-400">Overall Conversion Rate</div>
        <div class="text-2xl font-bold text-purple-600">{{ funnelStats.overall_conversion_rate }}%</div>
      </div>
      <div class="text-right">
        <div class="text-sm text-gray-600 dark:text-gray-400">Total Conversions</div>
        <div class="text-2xl font-bold text-purple-600">{{ funnelStats.total_conversions.toLocaleString() }}</div>
      </div>
    </div>
  </div>
</template>