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

interface SourceData {
  [key: string]: {
    visitors: number;
    clicks: number;
  };
}

interface Props {
  data: SourceData;
  height?: number;
}

const props = withDefaults(defineProps<Props>(), {
  height: 280
});

const sourceColors = {
  twitter: '#1DA1F2',
  youtube: '#FF0000',
  instagram: '#E4405F',
  tiktok: '#000000',
  google: '#4285F4',
  facebook: '#1877F2',
  linkedin: '#0077B5',
  reddit: '#FF4500',
  telegram: '#0088CC',
  email: '#34495E',
} as const;

const getSourceColor = (source: string): string => {
  const lowercaseSource = source.toLowerCase();
  return sourceColors[lowercaseSource as keyof typeof sourceColors] || '#6B7280';
};

const chartData = computed<ChartData<'doughnut'>>(() => {
  const sources = Object.keys(props.data);
  const visitors = sources.map(source => props.data[source].visitors);
  const colors = sources.map(source => getSourceColor(source));

  return {
    labels: sources.map(source => source.charAt(0).toUpperCase() + source.slice(1)),
    datasets: [
      {
        data: visitors,
        backgroundColor: colors,
        borderColor: colors.map(color => color + '40'),
        borderWidth: 2,
        hoverOffset: 4,
      }
    ]
  };
});

const chartOptions = computed<ChartOptions<'doughnut'>>(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
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
        afterLabel: (context) => {
          const source = Object.keys(props.data)[context.dataIndex];
          const sourceData = props.data[source];
          return [
            `Clicks: ${sourceData.clicks.toLocaleString()}`,
            `CTR: ${sourceData.clicks > 0 ? ((sourceData.clicks / sourceData.visitors) * 100).toFixed(1) : 0}%`
          ];
        }
      }
    }
  },
  cutout: '60%',
  animation: {
    animateRotate: true,
    duration: 1000,
    easing: 'easeOutQuart'
  }
}));

const totalVisitors = computed(() => {
  return Object.values(props.data).reduce((sum, source) => sum + source.visitors, 0);
});

const totalClicks = computed(() => {
  return Object.values(props.data).reduce((sum, source) => sum + source.clicks, 0);
});

const avgCTR = computed(() => {
  if (totalVisitors.value === 0) return 0;
  return ((totalClicks.value / totalVisitors.value) * 100).toFixed(1);
});
</script>

<template>
  <div class="relative">
    <div :style="{ height: `${height}px` }" class="relative">
      <Doughnut :data="chartData" :options="chartOptions" />

      <!-- Center Stats -->
      <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
        <div class="text-center">
          <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ totalVisitors.toLocaleString() }}</div>
          <div class="text-sm text-gray-600 dark:text-gray-400">Total Visitors</div>
          <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">{{ avgCTR }}% avg CTR</div>
        </div>
      </div>
    </div>

    <!-- No Data State -->
    <div v-if="Object.keys(data).length === 0" class="absolute inset-0 flex items-center justify-center">
      <div class="text-center text-gray-500 dark:text-gray-400">
        <div class="text-sm">No source data available</div>
        <div class="text-xs mt-1">Add UTM parameters to track sources</div>
      </div>
    </div>
  </div>
</template>