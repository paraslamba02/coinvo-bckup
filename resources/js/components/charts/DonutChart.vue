<script setup lang="ts">
import { Doughnut } from 'vue-chartjs';
import {
  Chart as ChartJS,
  ArcElement,
  Tooltip,
  Legend,
  ChartOptions,
  ChartData
} from 'chart.js';
import { computed } from 'vue';

ChartJS.register(ArcElement, Tooltip, Legend);

interface Props {
  data: Record<string, number>;
  colors?: string[];
  showLegend?: boolean;
  height?: number;
}

const props = withDefaults(defineProps<Props>(), {
  colors: () => ['#3B82F6', '#60A5FA', '#93C5FD', '#DBEAFE', '#1E40AF'],
  showLegend: true,
  height: 300
});

const chartData = computed<ChartData<'doughnut'>>(() => {
  const labels = Object.keys(props.data);
  const values = Object.values(props.data);
  
  return {
    labels,
    datasets: [
      {
        data: values,
        backgroundColor: props.colors.slice(0, labels.length),
        borderWidth: 0,
        hoverBorderWidth: 2,
        hoverBorderColor: '#ffffff',
      }
    ]
  };
});

const chartOptions = computed<ChartOptions<'doughnut'>>(() => ({
  responsive: true,
  maintainAspectRatio: false,
  cutout: '60%',
  plugins: {
    legend: {
      display: props.showLegend,
      position: 'right' as const,
      labels: {
        usePointStyle: true,
        padding: 20,
        font: {
          size: 12
        }
      }
    },
    tooltip: {
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      titleColor: '#ffffff',
      bodyColor: '#ffffff',
      borderColor: 'rgba(255, 255, 255, 0.1)',
      borderWidth: 1,
      callbacks: {
        label: (context) => {
          const total = context.dataset.data.reduce((a, b) => Number(a) + Number(b), 0);
          const percentage = ((Number(context.parsed) / Number(total)) * 100).toFixed(1);
          return `${context.label}: ${context.parsed} (${percentage}%)`;
        }
      }
    }
  },
  hover: {
    animationDuration: 200
  },
  animation: {
    animateRotate: true,
    duration: 800
  }
}));
</script>

<template>
  <div :style="{ height: `${height}px` }">
    <Doughnut :data="chartData" :options="chartOptions" />
  </div>
</template>