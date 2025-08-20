"use client";
import { useMemo } from "react";
import dynamic from "next/dynamic";
import useSWR from "swr";

interface Position {
  id: number;
  device_id: number;
  latitude: number;
  longitude: number;
  speed: number;
  device_time: string;
  attributes?: any;
}

interface Device {
  id: number;
  name: string;
  status: string;
  lastUpdate: string;
}

const fetcher = (url: string) => fetch(url).then(r => r.json()).then(res => res.data || []);
const LiveMap = dynamic(() => import("../components/LiveMap"), { ssr: false });

export default function Home() {
  // Remove trailing slash from API URL to prevent double slashes
  const apiBase = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000";
  const api = apiBase.endsWith('/') ? apiBase.slice(0, -1) : apiBase;
  
  // Fetch real-time data with better error handling
  const { data: positions, error: positionsError, isLoading: positionsLoading } = useSWR<Position[]>(
    `${api}/api/traccar/positions-cached`, 
    fetcher, 
    { 
      refreshInterval: 5000,
      revalidateOnFocus: false,
      dedupingInterval: 2000,
      errorRetryCount: 3,
      errorRetryInterval: 5000
    }
  );
  
  const { data: devices, error: devicesError, isLoading: devicesLoading } = useSWR<Device[]>(
    `${api}/api/traccar/devices`, 
    fetcher, 
    { 
      refreshInterval: 30000,
      revalidateOnFocus: false,
      dedupingInterval: 10000,
      errorRetryCount: 3,
      errorRetryInterval: 10000
    }
  );

  // Process analytics data
  const analytics = useMemo(() => {
    if (!positions?.length || !devices?.length) return null;

    // Use all positions regardless of timestamp for testing
    const speeds = positions.map(p => p.speed || 0);
    const avgSpeed = speeds.length ? speeds.reduce((a, b) => a + b, 0) / speeds.length : 0;
    const maxSpeed = speeds.length ? Math.max(...speeds) : 0;
    const activeDevices = devices.length; // Show all devices

    // Top performers (show all devices with at least one position)
    const deviceMetrics = devices.map(device => {
      const devicePositions = positions.filter(p => p.device_id === device.id);
      if (devicePositions.length === 0) return null;

      const deviceSpeeds = devicePositions.map(p => p.speed || 0);
      const deviceAvgSpeed = deviceSpeeds.length ? deviceSpeeds.reduce((a, b) => a + b, 0) / deviceSpeeds.length : 0;
      const efficiency = Math.min((deviceAvgSpeed / 40) * 100, 100);

      return {
        name: device.name,
        avgSpeed: Math.round(deviceAvgSpeed * 10) / 10,
        efficiency: Math.round(efficiency),
        status: device.status
      };
    }).filter((item): item is NonNullable<typeof item> => item !== null)
      .sort((a, b) => b.efficiency - a.efficiency);

    return {
      avgSpeed: Math.round(avgSpeed * 10) / 10,
      maxSpeed: Math.round(maxSpeed),
      activeDevices,
      totalDevices: devices.length,
      topPerformers: deviceMetrics.slice(0, 3)
    };
  }, [positions, devices]);

  const hasError = positionsError || devicesError;
  const isLoading = positionsLoading || devicesLoading;

  // Debug information for development
  console.log('API URL:', api);
  console.log('Positions:', positions?.length || 0);
  console.log('Devices:', devices?.length || 0);
  console.log('Errors:', { positionsError, devicesError });

  return (
    <div className="dashboard">
      {/* Header */}
      <div className="header">
        <div className="header-left">
          <h1>Thumbworx Tracker</h1>
        </div>
        <div className="header-stats">
          <div className="stat-item">
            <span className="stat-value">{analytics?.activeDevices || 0}</span>
            <span className="stat-label">Active</span>
          </div>
          <div className="stat-item">
            <span className="stat-value">{analytics?.avgSpeed || 0}</span>
            <span className="stat-label">Avg km/h</span>
          </div>
          <div className={`connection-status ${hasError ? 'error' : 'connected'}`}>
            <span>{hasError ? '🔴 Disconnected' : '🟢 Connected'}</span>
          </div>
        </div>
      </div>

      {/* Main Content Grid */}
      <div className="main-grid">
        {/* Map Section */}
        <div className="map-section">
          {hasError ? (
            <div className="error-state">
              <h3>🔌 Connection Error</h3>
              <p>Unable to connect to the tracking server.</p>
              <p>API: {api}</p>
              <button onClick={() => window.location.reload()} className="retry-btn">
                Retry Connection
              </button>
            </div>
          ) : isLoading ? (
            <div className="loading-state">
              <div className="loading-spinner"></div>
              <p>Connecting to tracking server...</p>
            </div>
          ) : (
            <LiveMap 
              positions={positions || []}
              devices={devices || []}
              isLoading={isLoading}
            />
          )}
        </div>

        {/* Analytics Sidebar */}
        <div className="analytics-sidebar">
          {/* Quick Metrics */}
          <div className="metrics-panel">
            <h3>Vehicle Metrics</h3>
            <div className="metrics-grid">
              <div className="metric-card primary">
                <div className="metric-value">{analytics?.avgSpeed || 0}</div>
                <div className="metric-label">Avg Speed</div>
              </div>
              <div className="metric-card">
                <div className="metric-value">{analytics?.maxSpeed || 0}</div>
                <div className="metric-label">Max Speed</div>
              </div>
              <div className="metric-card">
                <div className="metric-value">{analytics?.activeDevices || 0}</div>
                <div className="metric-label">Online</div>
              </div>
              <div className="metric-card">
                <div className="metric-value">{positions?.length || 0}</div>
                <div className="metric-label">Updates</div>
              </div>
            </div>
          </div>

          {/* Top Performers */}
          <div className="performers-panel">
            <h3>Top Performers</h3>
            {analytics?.topPerformers && analytics.topPerformers.length > 0 ? (
              analytics.topPerformers.map((performer, index) => (
                <div key={index} className="performer-row">
                  <div className="performer-rank">#{index + 1}</div>
                  <div className="performer-info">
                    <div className="performer-name">{performer.name}</div>
                    <div className="performer-stats">
                      {performer.avgSpeed} km/h • {performer.efficiency}%
                    </div>
                  </div>
                  <div className={`status-badge ${performer.status}`}>
                    {performer.status === 'online' ? '●' : '○'}
                  </div>
                </div>
              ))
            ) : (
              <div className="no-data">No performance data available</div>
            )}
          </div>
        </div>
      </div>

      <style jsx global>{`
        * {
          box-sizing: border-box;
        }
        
        body {
          margin: 0;
          padding: 0;
          font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif;
          background: #f8fafc;
        }

        .dashboard {
          min-height: 100vh;
          background: #ffffff;
        }

        .header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 8px 24px;
          color: #111827;
          background: #ffffff;
          border-bottom: 1px solid #e5e7eb;
          box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .header-left {
          display: flex;
          align-items: center;
          gap: 16px;
        }

        .header h1 {
          margin: 0;
          font-size: 1.25rem;
          font-weight: 600;
          color: #111827;
        }

        .live-indicator {
          display: flex;
          align-items: center;
          gap: 6px;
          background: #f9fafb;
          padding: 4px 8px;
          border-radius: 12px;
          font-size: 0.75rem;
          font-weight: 500;
          border: 1px solid #e5e7eb;
        }

        .status-dot {
          width: 6px;
          height: 6px;
          border-radius: 50%;
          animation: pulse 2s infinite;
        }

        .status-dot.live {
          background: #10b981;
        }

        .status-dot.error {
          background: #ef4444;
        }

        @keyframes pulse {
          0% { opacity: 1; transform: scale(1); }
          50% { opacity: 0.7; transform: scale(1.1); }
          100% { opacity: 1; transform: scale(1); }
        }

        .header-stats {
          display: flex;
          gap: 12px;
        }

        .stat-item {
          display: flex;
          flex-direction: column;
          align-items: center;
          background: #f9fafb;
          padding: 6px 12px;
          border-radius: 6px;
          border: 1px solid #e5e7eb;
        }

        .stat-value {
          font-size: 1rem;
          font-weight: 600;
          line-height: 1;
          color: #111827;
        }

        .stat-label {
          font-size: 0.7rem;
          color: #6b7280;
          margin-top: 2px;
        }

        .connection-status {
          display: flex;
          align-items: center;
          padding: 6px 12px;
          border-radius: 6px;
          font-size: 0.875rem;
          font-weight: 500;
        }

        .connection-status.connected {
          background: #dcfce7;
          color: #166534;
          border: 1px solid #bbf7d0;
        }

        .connection-status.error {
          background: #fef2f2;
          color: #991b1b;
          border: 1px solid #fecaca;
        }

        .error-state,
        .loading-state {
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          height: 100%;
          text-align: center;
          color: #6b7280;
        }

        .error-state h3 {
          color: #ef4444;
          margin-bottom: 12px;
        }

        .retry-btn {
          background: #3b82f6;
          color: white;
          border: none;
          padding: 8px 16px;
          border-radius: 6px;
          cursor: pointer;
          margin-top: 12px;
          font-weight: 500;
        }

        .retry-btn:hover {
          background: #2563eb;
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

        .main-grid {
          display: grid;
          grid-template-columns: 1fr 280px;
          gap: 16px;
          padding: 16px;
          height: calc(100vh - 56px);
        }

        .map-section {
          background: #ffffff;
          border-radius: 6px;
          padding: 16px;
          border: 1px solid #e5e7eb;
        }

        .analytics-sidebar {
          display: flex;
          flex-direction: column;
          gap: 12px;
          overflow-y: auto;
        }

        .metrics-panel,
        .performers-panel {
          background: #ffffff;
          border-radius: 6px;
          padding: 16px;
          border: 1px solid #e5e7eb;
        }

        .metrics-panel h3,
        .performers-panel h3 {
          margin: 0 0 12px 0;
          font-size: 0.875rem;
          color: #111827;
          font-weight: 600;
        }

        .metrics-grid {
          display: grid;
          grid-template-columns: 1fr 1fr;
          gap: 8px;
        }

        .metric-card {
          background: #f9fafb;
          padding: 12px;
          border-radius: 4px;
          text-align: center;
          border: 1px solid #e5e7eb;
        }

        .metric-card.primary {
          background: #111827;
          color: white;
        }

        .metric-value {
          font-size: 1.125rem;
          font-weight: 600;
          margin-bottom: 4px;
        }

        .metric-label {
          font-size: 0.7rem;
          opacity: 0.8;
          font-weight: 500;
        }

        .performer-row {
          display: flex;
          align-items: center;
          gap: 10px;
          padding: 8px 0;
          border-bottom: 1px solid #f3f4f6;
        }

        .performer-row:last-child {
          border-bottom: none;
        }

        .performer-rank {
          width: 20px;
          height: 20px;
          border-radius: 50%;
          background: #111827;
          color: white;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 0.7rem;
          font-weight: 600;
        }

        .performer-info {
          flex: 1;
        }

        .performer-name {
          font-weight: 600;
          color: #111827;
          font-size: 0.8rem;
          margin-bottom: 2px;
        }

        .performer-stats {
          font-size: 0.7rem;
          color: #6b7280;
        }

        .status-badge {
          font-size: 0.875rem;
        }

        .status-badge.online {
          color: #10b981;
        }

        .status-badge.offline {
          color: #6b7280;
        }

        .no-data {
          text-align: center;
          color: #9ca3af;
          font-style: italic;
          padding: 12px;
          font-size: 0.8rem;
        }

        @media (max-width: 1200px) {
          .main-grid {
            grid-template-columns: 1fr;
            grid-template-rows: 60vh auto;
          }
          
          .analytics-sidebar {
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            display: grid;
          }
        }

        @media (max-width: 768px) {
          .header {
            flex-direction: column;
            gap: 12px;
            padding: 12px 16px;
          }
          
          .header-left {
            flex-direction: column;
            gap: 8px;
          }
          
          .header h1 {
            font-size: 1.5rem;
          }
          
          .main-grid {
            padding: 12px;
            gap: 12px;
          }
          
          .analytics-sidebar {
            grid-template-columns: 1fr;
          }
        }
      `}</style>
    </div>
  );
}
