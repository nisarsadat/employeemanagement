// src/repositories/useDetailPdfRepository.js
import { ref } from 'vue';

let fontsLoaded = false;

async function b64(path) {
  const res = await fetch(path, { cache: 'no-cache' });
  if (!res.ok) throw new Error(`Font fetch failed ${res.status} @ ${path}`);
  const buf = await res.arrayBuffer();

  let bin = '';
  const bytes = new Uint8Array(buf);
  const chunk = 0x8000;
  for (let i = 0; i < bytes.length; i += chunk) {
    bin += String.fromCharCode.apply(null, bytes.subarray(i, i + chunk));
  }
  return btoa(bin);
}

async function imgB64(url) {
  if (!url) return null;
  const res = await fetch(url, { cache: 'no-cache' });
  if (!res.ok) return null;
  const blob = await res.blob();

  return await new Promise((resolve) => {
    const fr = new FileReader();
    fr.onloadend = () => resolve(fr.result);
    fr.readAsDataURL(blob);
  });
}

async function ensureFonts(pdfMake) {
  if (fontsLoaded) return;

  const [reg, bold] = await Promise.all([
    b64('/fonts/Amiri-Regular.ttf'),
    b64('/fonts/Amiri-Bold.ttf'),
  ]);

  pdfMake.vfs = {
    ...(pdfMake.vfs || {}),
    'Amiri-Regular.ttf': reg,
    'Amiri-Bold.ttf': bold,
  };

  pdfMake.fonts = {
    Amiri: {
      normal: 'Amiri-Regular.ttf',
      bold: 'Amiri-Bold.ttf',
      italics: 'Amiri-Regular.ttf',
      bolditalics: 'Amiri-Bold.ttf',
    },
  };

  fontsLoaded = true;
}

/**
 * Generic repo for building a "details" PDF.
 *
 * @example
 * await downloadDetailPdf({
 *   title: fullName + ' جزییات',
 *   fileName: fullName,
 *   imageSrc: imageSrc.value,
 *   pairs: [
 *     { label: 'نوم', value: 'احمد' },
 *     { label: 'پلار نوم', value: 'عبدالله' },
 *     ...
 *   ],
 * });
 */
export function useDetailPdfRepository() {
  const downloading = ref(false);

  function buildFourColumnBody(pairs) {
    const body = [];
    for (let i = 0; i < pairs.length; i += 2) {
      const p1 = pairs[i];
      const p2 = pairs[i + 1];

      const row = [
        { text: p1.label, bold: true, margin: [0, 2, 0, 2] },
        { text: p1.value ?? '—', margin: [0, 2, 0, 2] },
      ];

      if (p2) {
        row.push(
          { text: p2.label, bold: true, margin: [0, 2, 0, 2] },
          { text: p2.value ?? '—', margin: [0, 2, 0, 2] },
        );
      } else {
        row.push({ text: '', margin: [0, 2, 0, 2] }, { text: '', margin: [0, 2, 0, 2] });
      }

      body.push(row);
    }
    return body;
  }

  async function downloadDetailPdf({ title, fileName, imageSrc, pairs }) {
    if (!pairs || !Array.isArray(pairs)) {
      console.error('downloadDetailPdf: `pairs` must be an array');
      return;
    }

    try {
      downloading.value = true;
      const pdfMake = (await import('pdfmake/build/pdfmake.js')).default;

      await ensureFonts(pdfMake);

      const content = [];

      // سرلیک
      content.push({
        text: title || 'جزییات',
        style: 'title',
        margin: [0, 0, 0, 10],
      });

      // انځور (اختیاري)
      const img = await imgB64(imageSrc);
      if (img) {
        content.push({
          image: img,
          width: 130,
          alignment: 'center',
          margin: [0, 0, 0, 10],
        });
      }

      // ۴ ستنه جدول
      content.push({
        table: {
          widths: ['auto', '*', 'auto', '*'],
          body: buildFourColumnBody(pairs),
        },
        layout: {
          hLineWidth: () => 0.4,
          vLineWidth: () => 0.0,
          hLineColor: () => '#e5e5e5',
          paddingLeft: () => 6,
          paddingRight: () => 6,
          paddingTop: () => 4,
          paddingBottom: () => 4,
        },
      });

      const doc = {
        pageSize: 'A4',
        pageMargins: [28, 28, 28, 28],
        defaultStyle: { font: 'Amiri', fontSize: 12 },
        styles: { title: { fontSize: 16, bold: true, alignment: 'right' } },
        content,
        rtl: true,
      };

      const safeName = (fileName || 'detail')
        .toString()
        .replace(/[^\w\u0600-\u06FF\-]+/g, '_');

      pdfMake.createPdf(doc).download(`${safeName}.pdf`);
    } catch (err) {
      console.error('downloadDetailPdf failed:', err);
    } finally {
      downloading.value = false;
    }
  }

  return {
    downloading,
    downloadDetailPdf,
  };
}
