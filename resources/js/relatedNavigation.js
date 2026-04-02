export function buildRelationQuery({
  officeEmployeeId = null,
  resourceId = null,
  informationId = null,
  eventId = null,
  fiftySixFormatId = null,
  source = null,
  open = null,
} = {}) {
  return Object.fromEntries(
    Object.entries({
      office_employee_id: officeEmployeeId,
      resource_id: resourceId,
      information_id: informationId,
      event_id: eventId,
      fifty_six_format_id: fiftySixFormatId,
      source,
      open,
    }).filter(([_, v]) => v !== null && v !== undefined && v !== '')
  );
}