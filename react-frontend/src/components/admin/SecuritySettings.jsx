/**
 * Security Settings Component
 * Configure security-related system settings
 */

import { useState, useEffect } from 'react';

function SecuritySettings() {
  const [settings, setSettings] = useState([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');
  const [formData, setFormData] = useState({});

  useEffect(() => {
    fetchSettings();
  }, []);

  const fetchSettings = async () => {
    try {
      setLoading(true);
      const response = await fetch('/custom/api/settings.php?category=security', {
        credentials: 'include'
      });

      if (!response.ok) throw new Error('Failed to fetch settings');

      const data = await response.json();
      setSettings(data.settings || []);

      // Initialize form data
      const initialData = {};
      data.settings.forEach(setting => {
        initialData[setting.setting_key] = setting.setting_value;
      });
      setFormData(initialData);

    } catch (err) {
      console.error('Error fetching settings:', err);
      setError('Failed to load security settings');
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (key, value) => {
    setFormData(prev => ({
      ...prev,
      [key]: value
    }));
  };

  const handleSave = async () => {
    try {
      setSaving(true);
      setError('');
      setMessage('');

      const response = await fetch('/custom/api/settings.php', {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
        },
        credentials: 'include',
        body: JSON.stringify({
          settings: formData
        })
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'Failed to save settings');
      }

      setMessage('Security settings updated successfully');
      setTimeout(() => setMessage(''), 3000);

    } catch (err) {
      console.error('Error saving settings:', err);
      setError(err.message || 'Failed to save settings');
    } finally {
      setSaving(false);
    }
  };

  const renderSettingInput = (setting) => {
    const key = setting.setting_key;
    const value = formData[key] || setting.setting_value;

    if (setting.setting_type === 'boolean') {
      return (
        <input
          type="checkbox"
          checked={value === '1' || value === true}
          onChange={(e) => handleChange(key, e.target.checked ? '1' : '0')}
          disabled={!setting.is_editable}
          className="form-checkbox h-5 w-5 text-blue-600 rounded focus:ring-blue-500"
        />
      );
    } else if (setting.setting_type === 'integer') {
      return (
        <input
          type="number"
          value={value}
          onChange={(e) => handleChange(key, e.target.value)}
          disabled={!setting.is_editable}
          className="input-base w-32"
          min="0"
        />
      );
    } else {
      return (
        <input
          type="text"
          value={value}
          onChange={(e) => handleChange(key, e.target.value)}
          disabled={!setting.is_editable}
          className="input-base"
        />
      );
    }
  };

  const getSettingLabel = (key) => {
    return key.split('.').pop().split('_').map(word =>
      word.charAt(0).toUpperCase() + word.slice(1)
    ).join(' ');
  };

  if (loading) {
    return (
      <div className="text-center py-8">
        <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p className="mt-2 text-gray-600">Loading settings...</p>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-2xl font-bold text-gray-800">Security Settings</h2>
        <p className="text-gray-600 mt-1">Configure security and authentication parameters</p>
      </div>

      {message && (
        <div className="success-message">
          {message}
        </div>
      )}

      {error && (
        <div className="error-message">
          {error}
        </div>
      )}

      <div className="card">
        <div className="card-header">
          <h3 className="text-lg font-semibold text-gray-800">Account Lockout</h3>
        </div>
        <div className="card-body space-y-4">
          {settings.filter(s => s.setting_key.includes('login_attempts') || s.setting_key.includes('lockout')).map(setting => (
            <div key={setting.setting_key} className="flex items-center justify-between py-2">
              <div className="flex-1">
                <label className="block font-medium text-gray-700">
                  {getSettingLabel(setting.setting_key)}
                </label>
                <p className="text-sm text-gray-500 mt-1">{setting.description}</p>
              </div>
              <div className="ml-4">
                {renderSettingInput(setting)}
              </div>
            </div>
          ))}
        </div>
      </div>

      <div className="card">
        <div className="card-header">
          <h3 className="text-lg font-semibold text-gray-800">Password Requirements</h3>
        </div>
        <div className="card-body space-y-4">
          {settings.filter(s => s.setting_key.includes('password')).map(setting => (
            <div key={setting.setting_key} className="flex items-center justify-between py-2">
              <div className="flex-1">
                <label className="block font-medium text-gray-700">
                  {getSettingLabel(setting.setting_key)}
                </label>
                <p className="text-sm text-gray-500 mt-1">{setting.description}</p>
              </div>
              <div className="ml-4">
                {renderSettingInput(setting)}
              </div>
            </div>
          ))}
        </div>
      </div>

      <div className="card">
        <div className="card-header">
          <h3 className="text-lg font-semibold text-gray-800">Session Management</h3>
        </div>
        <div className="card-body space-y-4">
          {settings.filter(s => s.setting_key.includes('session')).map(setting => (
            <div key={setting.setting_key} className="flex items-center justify-between py-2">
              <div className="flex-1">
                <label className="block font-medium text-gray-700">
                  {getSettingLabel(setting.setting_key)}
                </label>
                <p className="text-sm text-gray-500 mt-1">{setting.description}</p>
              </div>
              <div className="ml-4">
                {renderSettingInput(setting)}
              </div>
            </div>
          ))}
        </div>
      </div>

      <div className="flex justify-end">
        <button
          onClick={handleSave}
          disabled={saving}
          className="btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {saving ? 'Saving...' : 'Save Changes'}
        </button>
      </div>
    </div>
  );
}

export default SecuritySettings;
