<script setup lang="ts">
import { Line } from 'vue-chartjs';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Filler,
  ChartOptions,
  ChartData
} from 'chart.js';
import { computed } from 'vue';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Filler);

interface Props {
  data: number[];
  color?: string;
  height?: number;
  showArea?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  color: '#9CA3AF',
  height: 40,
  showArea: true
});

const chartData = computed<ChartData<'line'>>(() => {
  const labels = props.data.map((_, index) => `Day ${index + 1}`);
  
  return {
    labels,
    datasets: [
      {
        data: props.data,
        borderColor: props.color,
        backgroundColor: props.showArea ? `${props.color}20` : 'transparent',
        borderWidth: 1.5,
        fill: props.showArea,
        pointRadius: 0,
        pointHoverRadius: 0,
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
      enabled: false
    }
  },
  scales: {
    x: {
      display: false,
      grid: {
        display: false
      }
    },
    y: {
      display: false,
      grid: {
        display: false
      }
    }
  },
  elements: {
    point: {
      radius: 0
    }
  },
  animation: {
    duration: 600,
    easing: 'easeOutQuart'
  }
}));
</script>

<template>
  <div :style="{ height: `${height}px` }" class="opacity-60">
    <Line :data="chartData" :options="chartOptions" />
  </div>
</template>