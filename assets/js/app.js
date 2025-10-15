(function () {
  'use strict';

  const unitField = document.getElementById('unitField');
  const heightCm  = document.getElementById('height_cm');
  const weightKg  = document.getElementById('weight_kg');
  const heightFt  = document.getElementById('height_ft');
  const heightIn  = document.getElementById('height_in');
  const weightLb  = document.getElementById('weight_lb');
  const bmiValueEl = document.getElementById('bmiValue');
  const bmiCategoryEl = document.getElementById('bmiCategory');
  const resetBtn = document.getElementById('resetBtn');
  const canvas = document.getElementById('bmiChart');

  let chart;

  const toNumber = v => {
    const n = parseFloat(v);
    return Number.isFinite(n) ? n : null;
  };

  function computeMetricBmi(cm, kg){ const m = cm/100; return (!m||!kg||m<=0||kg<=0)?null: kg/(m*m); }
  function computeImperialBmi(ft, inch, lb){ const i=(ft*12)+inch; return (!i||!lb||i<=0||lb<=0)?null: 703*lb/(i*i); }
  function getCategory(b){ if(b==null) return '—'; if(b<18.5) return 'Underweight'; if(b<25) return 'Normal'; if(b<30) return 'Overweight'; if(b<35) return 'Obesity I'; if(b<40) return 'Obesity II'; return 'Obesity III'; }

  function initChart() {
    if (!canvas) return;

    const css = getComputedStyle(document.documentElement);
    const cUnder = css.getPropertyValue('--underweight').trim();
    const cNorm  = css.getPropertyValue('--normal').trim();
    const cOver  = css.getPropertyValue('--overweight').trim();
    const cObese = css.getPropertyValue('--obese').trim();

    const data = {
      labels: ['BMI'],
      datasets: [
        { label:'Underweight', data:[18.5], stack:'ranges', borderWidth:0, backgroundColor:cUnder },
        { label:'Normal',      data:[6.5],  stack:'ranges', borderWidth:0, backgroundColor:cNorm  },
        { label:'Overweight',  data:[5],    stack:'ranges', borderWidth:0, backgroundColor:cOver  },
        { label:'Obesity',     data:[100],  stack:'ranges', borderWidth:0, backgroundColor:cObese },
        { label:'You',         data:[0],    stack:'you',    backgroundColor:'rgba(0,0,0,0)', borderWidth:0 }
      ]
    };

    // Draw marker line only (no labels)
    const markerPlugin = {
      id: 'bmiMarker',
      afterDatasetsDraw(ci) {
        const { ctx, scales: { x, y } } = ci;
        const val = ci.data.datasets[4].data[0];
        const xPos = x.getPixelForValue(Math.min(Math.max(val, 0), 60));
        ctx.save();
        ctx.beginPath();
        ctx.moveTo(xPos, y.top);
        ctx.lineTo(xPos, y.bottom);
        ctx.lineWidth = 2;
        ctx.strokeStyle = '#212529';
        ctx.stroke();
        ctx.restore();
      }
    };

    chart = new Chart(canvas.getContext('2d'), {
      type: 'bar',
      data,
      options: {
        animation: false,
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        scales: {
          x: {
            stacked: true,
            min: 0,
            max: 60,
            grid: { display: false },
            ticks: { display: false },
            border: { display: false }
          },
          y: {
            stacked: true,
            grid: { display: false },
            ticks: { display: false },
            border: { display: false }
          }
        },
        plugins: { legend: { display: false }, tooltip: { enabled: false } },
        layout: { padding: { bottom: 0 } } // removed space for numbers
      },
      plugins: [markerPlugin]
    });
  }

  function updateChartValue(bmi) {
    if (!chart) return;
    const val = bmi == null ? 0 : Math.min(Math.max(bmi, 0), 60);
    chart.data.datasets[4].data[0] = val;
    chart.update('none');
  }

  function updateResult() {
    let bmi = null;
    if (unitField.value === 'metric') {
      const cm = toNumber(heightCm.value), kg = toNumber(weightKg.value);
      bmi = (cm && kg) ? computeMetricBmi(cm, kg) : null;
    } else {
      const ft = toNumber(heightFt.value), inch = toNumber(heightIn.value), lb = toNumber(weightLb.value);
      bmi = (ft!=null && inch!=null && lb!=null) ? computeImperialBmi(ft, inch, lb) : null;
    }

    if (bmi != null) {
      const rounded = Math.round(bmi * 10) / 10;
      bmiValueEl.textContent = rounded.toFixed(1);
      bmiCategoryEl.textContent = getCategory(rounded);
      updateChartValue(rounded);
    } else {
      bmiValueEl.textContent = '—';
      bmiCategoryEl.textContent = '—';
      updateChartValue(null);
    }
  }

  $(document).ready(function(){
    initChart();
    updateResult();
    $('#metric-tab').on('shown.bs.tab', function(){ unitField.value='metric'; updateResult(); });
    $('#imperial-tab').on('shown.bs.tab', function(){ unitField.value='imperial'; updateResult(); });
    $('#height_cm, #weight_kg, #height_ft, #height_in, #weight_lb').on('input', updateResult);
    $(resetBtn).on('click', function(){ $('#bmiForm')[0].reset(); updateResult(); });
  });
})();