import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { api } from '@/lib/api';
import { useState, useEffect } from 'react';
import { toast } from 'sonner';
import { Save, Plus, Trash2, MapPin, Mail, Phone, Link2 } from 'lucide-react';

type SubTabType = 'menus' | 'info';

interface MenuLocation {
  location: string;
  name: string;
  menu: number | null;
}

interface SocialLink {
  id: string;
  label: string;
  url: string;
  icon: string;
}

interface ContactInfo {
  primaryEmail: string;
  primaryPhone: string;
  address: string;
}

interface GeneralSettingsData {
  siteName: string;
  siteDescription: string;
  menus: MenuLocation[];
  socialLinks: SocialLink[];
  contactInfo: ContactInfo;
}

export function GeneralSettings() {
  const queryClient = useQueryClient();
  const [activeSubTab, setActiveSubTab] = useState<SubTabType>('menus');
  const [isSaving, setIsSaving] = useState(false);

  // Fetch available menus for dropdown
  const { data: availableMenus = [] } = useQuery({
    queryKey: ['available-menus'],
    queryFn: async () => {
      const response = await fetch(`${window.location.origin}/wp-json/flatwp/v1/menus`, {
        credentials: 'same-origin',
      });
      return response.json();
    },
  });

  // Fetch current settings
  const { data: settings = {}, isLoading } = useQuery({
    queryKey: ['general-settings'],
    queryFn: async () => {
      const response = await fetch(`${window.location.origin}/wp-json/flatwp/v1/settings`, {
        credentials: 'same-origin',
      });
      return response.json();
    },
  });

  const [formData, setFormData] = useState<GeneralSettingsData>({
    siteName: '',
    siteDescription: '',
    menus: [
      { location: 'primary-nav', name: 'Primary Navigation', menu: null },
      { location: 'footer-column-1', name: 'Footer - Column 1', menu: null },
      { location: 'footer-column-2', name: 'Footer - Column 2', menu: null },
      { location: 'footer-column-3', name: 'Footer - Column 3', menu: null },
      { location: 'footer-column-4', name: 'Footer - Column 4', menu: null },
      { location: 'mobile-menu', name: 'Mobile Menu', menu: null },
    ],
    socialLinks: [],
    contactInfo: {
      primaryEmail: '',
      primaryPhone: '',
      address: '',
    },
  });

  // Load settings when they arrive
  useEffect(() => {
    if (settings && Object.keys(settings).length > 0) {
      setFormData({
        siteName: settings.siteName || '',
        siteDescription: settings.siteDescription || '',
        menus: settings.menus || formData.menus,
        socialLinks: settings.socialLinks || [],
        contactInfo: settings.contactInfo || formData.contactInfo,
      });
    }
  }, [settings]);

  const saveSettingsMutation = useMutation({
    mutationFn: async (data: GeneralSettingsData) => {
      const response = await fetch(`${window.location.origin}/wp-json/flatwp/v1/settings`, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': (window as any).flatwpNonce || '',
        },
        body: JSON.stringify(data),
      });
      return response.json();
    },
    onSuccess: (response) => {
      toast.success(response.message || 'Settings saved successfully');
      queryClient.invalidateQueries({ queryKey: ['general-settings'] });
      setIsSaving(false);
    },
    onError: () => {
      toast.error('Failed to save settings');
      setIsSaving(false);
    },
  });

  const handleSave = (e: React.FormEvent) => {
    e.preventDefault();
    setIsSaving(true);
    saveSettingsMutation.mutate(formData);
  };

  const updateMenuAssignment = (locationIndex: number, menuId: number | null) => {
    const updatedMenus = [...formData.menus];
    updatedMenus[locationIndex].menu = menuId;
    setFormData(prev => ({ ...prev, menus: updatedMenus }));
  };

  const addSocialLink = () => {
    setFormData(prev => ({
      ...prev,
      socialLinks: [
        ...prev.socialLinks,
        {
          id: Math.random().toString(36).substr(2, 9),
          label: '',
          url: '',
          icon: '',
        },
      ],
    }));
  };

  const updateSocialLink = (index: number, field: keyof SocialLink, value: string) => {
    const updatedLinks = [...formData.socialLinks];
    updatedLinks[index] = { ...updatedLinks[index], [field]: value };
    setFormData(prev => ({ ...prev, socialLinks: updatedLinks }));
  };

  const removeSocialLink = (index: number) => {
    setFormData(prev => ({
      ...prev,
      socialLinks: prev.socialLinks.filter((_, i) => i !== index),
    }));
  };

  if (isLoading) {
    return <div className="p-6 text-center text-gray-500">Loading settings...</div>;
  }

  return (
    <div className="max-w-6xl mx-auto">
      {/* Header */}
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-gray-900">General Settings</h1>
        <p className="text-gray-600 mt-2">Configure your site information and menu locations</p>
      </div>

      {/* Main Tabs */}
      <div className="border-b border-gray-200 mb-6">
        <div className="flex gap-8">
          <button
            onClick={() => setActiveSubTab('menus')}
            className={`pb-4 px-1 border-b-2 font-medium text-sm transition ${
              activeSubTab === 'menus'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-600 hover:text-gray-900'
            }`}
          >
            <MapPin className="inline-block mr-2 h-4 w-4" />
            Menu Locations
          </button>
          <button
            onClick={() => setActiveSubTab('info')}
            className={`pb-4 px-1 border-b-2 font-medium text-sm transition ${
              activeSubTab === 'info'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-600 hover:text-gray-900'
            }`}
          >
            <Link2 className="inline-block mr-2 h-4 w-4" />
            Site Info
          </button>
        </div>
      </div>

      {/* Content */}
      <form onSubmit={handleSave}>
        {/* Menus Tab */}
        {activeSubTab === 'menus' && (
          <div className="space-y-6">
            <div className="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
              <p className="text-sm text-blue-800">
                Assign WordPress menus to these locations. Changes will appear automatically on your frontend.
              </p>
            </div>

            <div className="space-y-4">
              {formData.menus.map((location, index) => (
                <div key={location.location} className="border border-gray-200 rounded-lg p-4">
                  <label className="block text-sm font-medium text-gray-900 mb-2">
                    {location.name}
                    <span className="text-gray-500 ml-2 font-normal text-xs">{location.location}</span>
                  </label>
                  <select
                    value={location.menu || ''}
                    onChange={(e) => updateMenuAssignment(index, e.target.value ? parseInt(e.target.value) : null)}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                  >
                    <option value="">-- No menu assigned --</option>
                    {availableMenus.map((menu: any) => (
                      <option key={menu.id} value={menu.id}>
                        {menu.name}
                      </option>
                    ))}
                  </select>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Info Tab */}
        {activeSubTab === 'info' && (
          <div className="space-y-6">
            {/* Site Info */}
            <div className="bg-white border border-gray-200 rounded-lg p-6">
              <h3 className="text-lg font-medium text-gray-900 mb-4">Site Information</h3>

              <div className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Site Name
                  </label>
                  <input
                    type="text"
                    value={formData.siteName}
                    onChange={(e) => setFormData(prev => ({ ...prev, siteName: e.target.value }))}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Your site name"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Site Description
                  </label>
                  <textarea
                    value={formData.siteDescription}
                    onChange={(e) => setFormData(prev => ({ ...prev, siteDescription: e.target.value }))}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Brief description of your site"
                    rows={3}
                  />
                </div>
              </div>
            </div>

            {/* Contact Information */}
            <div className="bg-white border border-gray-200 rounded-lg p-6">
              <h3 className="text-lg font-medium text-gray-900 mb-4">Contact Information</h3>

              <div className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    <Mail className="inline-block h-4 w-4 mr-2" />
                    Primary Email
                  </label>
                  <input
                    type="email"
                    value={formData.contactInfo.primaryEmail}
                    onChange={(e) => setFormData(prev => ({
                      ...prev,
                      contactInfo: { ...prev.contactInfo, primaryEmail: e.target.value }
                    }))}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="your@email.com"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    <Phone className="inline-block h-4 w-4 mr-2" />
                    Primary Phone
                  </label>
                  <input
                    type="tel"
                    value={formData.contactInfo.primaryPhone}
                    onChange={(e) => setFormData(prev => ({
                      ...prev,
                      contactInfo: { ...prev.contactInfo, primaryPhone: e.target.value }
                    }))}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="+1 (555) 000-0000"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    <MapPin className="inline-block h-4 w-4 mr-2" />
                    Address
                  </label>
                  <textarea
                    value={formData.contactInfo.address}
                    onChange={(e) => setFormData(prev => ({
                      ...prev,
                      contactInfo: { ...prev.contactInfo, address: e.target.value }
                    }))}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="123 Main St, City, State 12345"
                    rows={3}
                  />
                </div>
              </div>
            </div>

            {/* Social Links */}
            <div className="bg-white border border-gray-200 rounded-lg p-6">
              <div className="flex items-center justify-between mb-4">
                <h3 className="text-lg font-medium text-gray-900">Social Links</h3>
                <button
                  type="button"
                  onClick={addSocialLink}
                  className="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700"
                >
                  <Plus className="h-4 w-4 mr-1" />
                  Add Link
                </button>
              </div>

              <div className="space-y-4">
                {formData.socialLinks.length === 0 ? (
                  <p className="text-sm text-gray-500">No social links added yet</p>
                ) : (
                  formData.socialLinks.map((link, index) => (
                    <div key={link.id} className="border border-gray-200 rounded-lg p-4">
                      <div className="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                        <input
                          type="text"
                          placeholder="Label (e.g., Facebook)"
                          value={link.label}
                          onChange={(e) => updateSocialLink(index, 'label', e.target.value)}
                          className="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        />
                        <input
                          type="url"
                          placeholder="URL"
                          value={link.url}
                          onChange={(e) => updateSocialLink(index, 'url', e.target.value)}
                          className="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        />
                        <input
                          type="text"
                          placeholder="Icon name (e.g., facebook)"
                          value={link.icon}
                          onChange={(e) => updateSocialLink(index, 'icon', e.target.value)}
                          className="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        />
                      </div>
                      <button
                        type="button"
                        onClick={() => removeSocialLink(index)}
                        className="inline-flex items-center text-red-600 hover:text-red-800 text-sm"
                      >
                        <Trash2 className="h-4 w-4 mr-1" />
                        Remove
                      </button>
                    </div>
                  ))
                )}
              </div>
            </div>
          </div>
        )}

        {/* Save Button */}
        <div className="mt-8 flex justify-end">
          <button
            type="submit"
            disabled={isSaving}
            className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <Save className="h-4 w-4 mr-2" />
            {isSaving ? 'Saving...' : 'Save Settings'}
          </button>
        </div>
      </form>
    </div>
  );
}
