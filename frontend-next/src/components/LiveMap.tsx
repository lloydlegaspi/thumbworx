"use client";
import { useEffect, useRef } from 'react';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Fix for default markers
delete (L.Icon.Default.prototype as any)._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
  iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
});

interface Position {
  id: number;
  device_id: number;
  latitude: number;
  longitude: number;
  speed: number;
  device_time: string;
}

interface Device {
  id: number;
  name: string;
  status: string;
}

interface LiveMapProps {
  positions: Position[];
  devices: Device[];
  isLoading: boolean;
}

export default function LiveMap({ positions, devices, isLoading }: LiveMapProps) {
  const mapRef = useRef<HTMLDivElement>(null);
  const mapInstanceRef = useRef<L.Map | null>(null);
  const markersRef = useRef<L.LayerGroup | null>(null);

  useEffect(() => {
    if (!mapRef.current || mapInstanceRef.current) return;

    // Initialize map
    mapInstanceRef.current = L.map(mapRef.current).setView([14.5995, 120.9842], 11);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap contributors'
    }).addTo(mapInstanceRef.current);

    markersRef.current = L.layerGroup().addTo(mapInstanceRef.current);

    return () => {
      if (mapInstanceRef.current) {
        mapInstanceRef.current.remove();
        mapInstanceRef.current = null;
      }
    };
  }, []);

  useEffect(() => {
    if (!mapInstanceRef.current || !positions.length) return;

    // Clear existing markers
    if (markersRef.current) {
      markersRef.current.clearLayers();
    }

    // Create markers
    positions.forEach(position => {
      if (!position.latitude || !position.longitude) return;

      const device = devices.find(d => d.id === position.device_id);
      const deviceName = device?.name || `Vehicle ${position.device_id}`;
      const isOnline = device?.status === 'online';

      // Create custom icon based on speed
      const iconColor = position.speed > 50 ? '#ef4444' : 
                       position.speed > 20 ? '#f59e0b' : '#10b981';

      const customIcon = L.divIcon({
        html: `
          <div style="
            background: ${iconColor};
            border: 2px solid white;
            border-radius: 50%;
            width: 16px;
            height: 16px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.3);
          "></div>
        `,
        className: 'custom-marker',
        iconSize: [16, 16],
        iconAnchor: [8, 8]
      });

      const marker = L.marker([position.latitude, position.longitude], { 
        icon: customIcon 
      });

      const popupContent = `
        <div style="font-family: Arial, sans-serif; min-width: 150px;">
          <h3 style="margin: 0 0 8px 0; color: #333;">${deviceName}</h3>
          <p style="margin: 2px 0;"><strong>Speed:</strong> ${position.speed || 0} km/h</p>
          <p style="margin: 2px 0;"><strong>Status:</strong> ${isOnline ? '🟢 Online' : '🔴 Offline'}</p>
          <p style="margin: 2px 0;"><strong>Time:</strong> ${new Date(position.device_time).toLocaleTimeString()}</p>
        </div>
      `;

      marker.bindPopup(popupContent);
      markersRef.current?.addLayer(marker);
    });

    // Auto-fit bounds to show all positions
    if (markersRef.current) {
      const layers = markersRef.current.getLayers();
      if (layers.length > 0) {
        const group = L.featureGroup(layers);
        if (group.getBounds().isValid()) {
          mapInstanceRef.current.fitBounds(group.getBounds(), { padding: [20, 20] });
        }
      }
    }
  }, [positions, devices]);

  if (isLoading) {
    return (
      <div className="map-loading">
        <div className="loading-spinner"></div>
        <p>Loading map...</p>
        
        <style jsx>{`
          .map-loading {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #6b7280;
          }
          
          .loading-spinner {
            width: 32px;
            height: 32px;
            border: 3px solid #f3f4f6;
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 12px;
          }
          
          @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
          }
        `}</style>
      </div>
    );
  }

  return (
    <div className="map-container">
      <div ref={mapRef} className="map"></div>
      
      <style jsx>{`
        .map-container {
          height: 100%;
          width: 100%;
          border-radius: 6px;
          overflow: hidden;
        }
        
        .map {
          height: 100%;
          width: 100%;
        }
      `}</style>
    </div>
  );
}
