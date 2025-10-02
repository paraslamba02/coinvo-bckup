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
    conversions: number;
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
        afterLabel: (context) => {
          const source = Object.keys(props.data)[context.dataIndex];
          const sourceData = props.data[source];
          const ctr = sourceData.visitors > 0 ? ((sourceData.clicks / sourceData.visitors) * 100).toFixed(1) : 0;
          const conversionRate = sourceData.visitors > 0 ? ((sourceData.conversions / sourceData.visitors) * 100).toFixed(1) : 0;
          return [
            `Clicks: ${sourceData.clicks.toLocaleString()}`,
            `Signups: ${sourceData.conversions.toLocaleString()}`,
            `CTR: ${ctr}%`,
            `Conversion: ${conversionRate}%`
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

const totalConversions = computed(() => {
  return Object.values(props.data).reduce((sum, source) => sum + source.conversions, 0);
});

const avgCTR = computed(() => {
  if (totalVisitors.value === 0) return 0;
  return ((totalClicks.value / totalVisitors.value) * 100).toFixed(1);
});

const avgConversionRate = computed(() => {
  if (totalVisitors.value === 0) return 0;
  return ((totalConversions.value / totalVisitors.value) * 100).toFixed(1);
});
</script>

<template>
  <div class="relative">
    <!-- Stats Summary -->
    <div class="grid grid-cols-2 gap-4 text-sm mb-4">
      <div class="text-center">
        <div class="font-medium">{{ totalVisitors.toLocaleString() }}</div>
        <div class="text-muted-foreground">Total Visitors</div>
      </div>
      <div class="text-center">
        <div class="font-medium">{{ totalConversions.toLocaleString() }}</div>
        <div class="text-muted-foreground">Total Signups</div>
      </div>
      <div class="text-center">
        <div class="font-medium">{{ avgCTR }}%</div>
        <div class="text-muted-foreground">Avg CTR</div>
      </div>
      <div class="text-center">
        <div class="font-medium">{{ avgConversionRate }}%</div>
        <div class="text-muted-foreground">Avg Conversion</div>
      </div>
    </div>

    <!-- Chart Container -->
    <div :style="{ height: `${height - 80}px` }" class="relative">
      <Doughnut :data="chartData" :options="chartOptions" />
    </div>

    <!-- No Data State -->
    <div v-if="Object.keys(data).length === 0" class="absolute inset-0 flex items-center justify-center">
      <div class="text-center text-gray-500 dark:text-gray-400">
        <div class="text-sm">No source data available</div>
        <div class="text-xs mt-1">Create tracking links with sources to track traffic</div>
      </div>
    </div>
  </div>
</template>