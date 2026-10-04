export function foulActivitySections(report, escapeHtml) {
  const meetings = Array.isArray(report?.meetings?.rows) ? report.meetings.rows : [];
  const assessments = Array.isArray(report?.security_assessments?.rows)
    ? report.security_assessments.rows
    : [];
  const meetingsTotal = Number(report?.meetings?.total ?? 0) || 0;
  const assessmentsTotal = Number(report?.security_assessments?.total ?? 0) || 0;

  const meetingRows = meetings
    .map((row) => {
      return `
        <tr>
          <td>${escapeHtml(row?.meeting_title ?? '')}</td>
          <td>${escapeHtml(row?.organ ?? '')}</td>
          <td>${escapeHtml(row?.province ?? '')}</td>
          <td>${escapeHtml(row?.district ?? '')}</td>
          <td>${escapeHtml(row?.start_date ?? '')}</td>
        </tr>
      `;
    })
    .join('');

  const assessmentRows = assessments
    .map((row) => {
      return `
        <tr>
          <td>${escapeHtml(row?.place_name ?? '')}</td>
          <td>${escapeHtml(row?.responsible_owner_name ?? '')}</td>
          <td>${escapeHtml(row?.province ?? '')}</td>
          <td>${escapeHtml(row?.district ?? '')}</td>
        </tr>
      `;
    })
    .join('');

  const meetingsSection =
    meetingsTotal > 0
      ? `
        <div class="section">
          <div class="card">
            <div class="header-strip bg-primary">
              <div><div class="strip-title">غونډې</div><div class="strip-sub">د دې مودې غونډې</div></div>
              <span class="chip chip-primary">${meetingsTotal}</span>
            </div>
            <div style="padding:0;">
              <table>
                <thead>
                  <tr>
                    <th>عنوان</th>
                    <th>ارگان</th>
                    <th>ولایت</th>
                    <th>ولسوالی</th>
                    <th>نېټه</th>
                  </tr>
                </thead>
                <tbody>${meetingRows}</tbody>
              </table>
            </div>
          </div>
        </div>
      `
      : '';

  const assessmentsSection =
    assessmentsTotal > 0
      ? `
        <div class="section">
          <div class="card">
            <div class="header-strip bg-success">
              <div><div class="strip-title">امنیتي ارزونې</div><div class="strip-sub">د دې مودې امنیتي ارزونې</div></div>
              <span class="chip chip-success">${assessmentsTotal}</span>
            </div>
            <div style="padding:0;">
              <table>
                <thead>
                  <tr>
                    <th>د ځای نوم</th>
                    <th>مسؤول</th>
                    <th>ولایت</th>
                    <th>ولسوالی</th>
                  </tr>
                </thead>
                <tbody>${assessmentRows}</tbody>
              </table>
            </div>
          </div>
        </div>
      `
      : '';

  return `${meetingsSection}${assessmentsSection}`;
}
