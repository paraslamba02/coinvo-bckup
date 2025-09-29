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

interface Props {
  data: Record<string, number>;
  height?: number;
}

const props = withDefaults(defineProps<Props>(), {
  height: 280
});

const deviceColors = {
  mobile: '#10B981',    // Green
  desktop: '#3B82F6',   // Blue
  tablet: '#F59E0B',    // Amber
  tv: '#8B5CF6',        // Purple
};

const getDeviceColor = (device: string): string => {
  const lowercaseDevice = device.toLowerCase();
  return deviceColors[lowercaseDevice as keyof typeof deviceColors] || '#6B7280';
};

const chartData = computed<ChartData<'bar'>>(() => {
  const devices = Object.keys(props.data);
  const visitors = Object.values(props.data);
  const colors = devices.map(device => getDeviceColor(device));

  return {
    labels: devices.map(device => device.charAt(0).toUpperCase() + device.slice(1)),
    datasets: [
      {
        label: 'Visitors',
        data: visitors,
        backgroundColor: colors.map(color => color + '80'),
        borderColor: colors,
        borderWidth: 2,
        borderRadius: 6,
        borderSkipped: false,
      }
    ]
  };
});

const chartOptions = computed<ChartOptions<'bar'>>(() => ({
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
          const total = Object.values(props.data).reduce((sum, val) => sum + val, 0);
          const percentage = total > 0 ? ((context.parsed.y / total) * 100).toFixed(1) : 0;
          return `${percentage}% of total visitors`;
        }
      }
    }
  },
  scales: {
    y: {
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
    x: {
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

const totalVisitors = computed(() => {
  return Object.values(props.data).reduce((sum, visitors) => sum + visitors, 0);
});

const topDevice = computed(() => {
  if (Object.keys(props.data).length === 0) return null;

  const sortedDevices = Object.entries(props.data).sort(([,a], [,b]) => b - a);
  const [device, visitors] = sortedDevices[0];
  const percentage = totalVisitors.value > 0 ? ((visitors / totalVisitors.value) * 100).toFixed(1) : 0;

  return {
    device: device.charAt(0).toUpperCase() + device.slice(1),
    visitors,
    percentage
  };
});
</script>

<template>
  <div class="space-y-4">
    <!-- Stats Summary -->
    <div v-if="topDevice" class="flex justify-between items-center text-sm">
      <div>
        <span class="text-muted-foreground">Top Device: </span>
        <span class="font-medium">{{ topDevice.device }}</span>
        <span class="text-muted-foreground"> ({{ topDevice.percentage }}%)</span>
      </div>
      <div class="text-muted-foreground">
        {{ totalVisitors.toLocaleString() }} total visitors
      </div>
    </div>

    <!-- Chart -->
    <div :style="{ height: `${height}px` }">
      <Bar :data="chartData" :options="chartOptions" />
    </div>

    <!-- No Data State -->
    <div v-if="Object.keys(data).length === 0" class="flex items-center justify-center h-40">
      <div class="text-center text-gray-500 dark:text-gray-400">
        <div class="text-sm">No device data available</div>
        <div class="text-xs mt-1">Device tracking will appear here</div>
      </div>
    </div>
  </div>
</template>