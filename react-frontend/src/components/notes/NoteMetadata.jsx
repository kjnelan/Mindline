/**
 * Mindline EMHR
 * NoteMetadata - Auto-populated note header with patient/provider/service info
 * Displays all non-clinical metadata in a clean, professional format
 *
 * Author: Kenneth J. Nelan
 * License: Proprietary and Confidential
 * Version: ALPHA - Phase 4
 *
 * Copyright © 2026 Sacred Wandering
 * Proprietary and Confidential
 */

import React from 'react';

/**
 * Props:
 * - patient: object - Patient demographics and info
 * - provider: object - Provider name, credentials, ID
 * - serviceInfo: object - Service type, location, duration
 * - diagnosis: string - Primary diagnosis from treatment plan
 * - serviceDate: string - Date of service
 */
function NoteMetadata({ patient, provider, serviceInfo, diagnosis, serviceDate }) {
  const formatDate = (dateStr) => {
    if (!dateStr) return 'N/A';
    const [year, month, day] = dateStr.split(/[-T]/);
    const date = new Date(year, month - 1, day);
    return date.toLocaleDateString('en-US', {
      weekday: 'short',
      month: 'short',
      day: 'numeric',
      year: 'numeric'
    });
  };

  const formatDuration = (minutes) => {
    if (!minutes) return 'Not set';
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    if (hours > 0 && mins > 0) return `${hours}h ${mins}m`;
    if (hours > 0) return `${hours}h`;
    return `${mins}m`;
  };

  // Clean up ICD-10 description - remove duplicates and extra spaces
  const cleanDiagnosisDescription = (description) => {
    if (!description) return '';
    // Many ICD-10 descriptions have duplicate text separated by multiple spaces
    const parts = description.split(/\s{3,}/);
    if (parts.length > 1) {
      return parts[1].trim();
    }
    return description.trim();
  };

  return (
    <div className="card-main mb-6">
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        {/* Patient Information */}
        <div>
          <h3 className="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">
            Patient Information
          </h3>
          <div className="space-y-2">
            <div className="flex items-baseline gap-2">
              <span className="text-sm font-medium text-gray-600">Name:</span>
              <span className="text-base font-semibold text-gray-900">
                {patient?.fname} {patient?.mname} {patient?.lname}
              </span>
            </div>
            <div className="flex items-baseline gap-2">
              <span className="text-sm font-medium text-gray-600">DOB:</span>
              <span className="text-sm text-gray-800">
                {patient?.DOB ? formatDate(patient.DOB) : 'N/A'}
                {patient?.DOB && (
                  <span className="ml-2 text-gray-500">
                    (Age {new Date().getFullYear() - new Date(patient.DOB).getFullYear()})
                  </span>
                )}
              </span>
            </div>
            {patient?.pid && (
              <div className="flex items-baseline gap-2">
                <span className="text-sm font-medium text-gray-600">Patient ID:</span>
                <span className="text-sm text-gray-800 font-mono">{patient.pid}</span>
              </div>
            )}
          </div>
        </div>

        {/* Provider & Service Information */}
        <div>
          <h3 className="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">
            Service Information
          </h3>
          <div className="space-y-2">
            <div className="flex items-baseline gap-2">
              <span className="text-sm font-medium text-gray-600">Provider:</span>
              <span className="text-base font-semibold text-gray-900">
                {provider?.fullName || provider?.fname + ' ' + provider?.lname}
                {provider?.credentials && (
                  <span className="ml-2 text-sm font-normal text-gray-600">
                    {provider.credentials}
                  </span>
                )}
              </span>
            </div>
            <div className="flex items-baseline gap-2">
              <span className="text-sm font-medium text-gray-600">Date:</span>
              <span className="text-sm text-gray-800 font-semibold">
                {formatDate(serviceDate)}
              </span>
            </div>
            <div className="flex items-baseline gap-2">
              <span className="text-sm font-medium text-gray-600">Type:</span>
              <span className="text-sm text-gray-800">
                {serviceInfo?.type || 'Individual Therapy'}
              </span>
            </div>
            <div className="flex items-baseline gap-2">
              <span className="text-sm font-medium text-gray-600">Location:</span>
              <span className="text-sm text-gray-800">
                {serviceInfo?.location || 'Office'}
              </span>
            </div>
            {serviceInfo?.duration && (
              <div className="flex items-baseline gap-2">
                <span className="text-sm font-medium text-gray-600">Duration:</span>
                <span className="text-sm text-gray-800">
                  {formatDuration(serviceInfo.duration)}
                </span>
              </div>
            )}
          </div>
        </div>
      </div>

      {/* Diagnosis */}
      {diagnosis && (
        <div className="mt-6 pt-4 border-t border-gray-200">
          <div className="flex items-baseline gap-2">
            <span className="text-sm font-semibold text-gray-600">Primary Diagnosis:</span>
            <span className="text-sm text-gray-900 font-medium">{cleanDiagnosisDescription(diagnosis)}</span>
          </div>
        </div>
      )}
    </div>
  );
}

export default NoteMetadata;
