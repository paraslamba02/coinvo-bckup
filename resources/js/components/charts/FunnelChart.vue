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
  step1_signups: number;
  step2_deposits: number;
  step3_rewards: number;
  step1_to_step2_rate: number;
  step2_to_step3_rate: number;
  overall_conversion_rate: number;
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
    props.funnelStats.step1_signups,
    props.funnelStats.step2_deposits,
    props.funnelStats.step3_rewards
  ];

  return {
    labels: ['Signups', 'Deposits', 'Rewards'],
    datasets: [
      {
        label: 'Users',
        data: data,
        backgroundColor: [
          'rgba(59, 130, 246, 0.8)',  // blue
          'rgba(34, 197, 94, 0.8)',   // green
          'rgba(147, 51, 234, 0.8)'   // purple
        ],
        borderColor: [
          'rgb(59, 130, 246)',
          'rgb(34, 197, 94)',
          'rgb(147, 51, 234)'
        ],
        borderWidth: 1,
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
          if (index === 1) {
            return `Conversion: ${props.funnelStats.step1_to_step2_rate}% from signups`;
          } else if (index === 2) {
            return `Conversion: ${props.funnelStats.step2_to_step3_rate}% from deposits`;
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
</script>

<template>
  <div :style="{ height: `${height}px` }">
    <Bar :data="chartData" :options="chartOptions" />
  </div>
</template>