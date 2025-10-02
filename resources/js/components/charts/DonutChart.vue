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
  layout: {
    padding: {
      top: 5,
      bottom: 5,
      left: 5,
      right: 5
    }
  },
  plugins: {
    legend: {
      display: props.showLegend,
      position: 'bottom' as const,
      labels: {
        usePointStyle: true,
        padding: 12,
        boxWidth: 10,
        boxHeight: 10,
        font: {
          size: 10
        },
        generateLabels: (chart) => {
          const original = ChartJS.defaults.plugins.legend.labels.generateLabels;
          const labels = original(chart);

          const data = chart.data;
          if (data.labels && data.datasets.length) {
            return labels.map((label, i) => {
              const dataset = data.datasets[0];
              const value = dataset.data[i] as number;
              const total = (dataset.data as number[]).reduce((a, b) => a + b, 0);
              const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;

              // Truncate long labels for display
              const displayLabel = typeof data.labels![i] === 'string' && (data.labels![i] as string).length > 15
                ? (data.labels![i] as string).substring(0, 15) + '...'
                : data.labels![i];

              return {
                ...label,
                text: `${displayLabel}: ${percentage}%`
              };
            });
          }
          return labels;
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
        title: (context) => {
          // Show full label in tooltip even if truncated in legend
          return context[0].label;
        },
        label: (context) => {
          const total = context.dataset.data.reduce((a, b) => Number(a) + Number(b), 0);
          const percentage = ((Number(context.parsed) / Number(total)) * 100).toFixed(1);
          return `Count: ${context.parsed} (${percentage}%)`;
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
  <div :style="{ height: `${height}px` }" class="relative overflow-visible">
    <Doughnut :data="chartData" :options="chartOptions" />
  </div>
</template>