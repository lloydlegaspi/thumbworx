"use client";
import { useEffect, useState } from "react";
import dynamic from "next/dynamic";
import useSWR from "swr";

const fetcher = (url: string) => fetch(url).then(r => r.json());
const MapWithNoSSR = dynamic(() => import("../components/Map"), { ssr: false });

export default function Home() {
  const api = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000";
  const { data, error } = useSWR(`${api}/api/traccar/positions`, fetcher, { refreshInterval: 5000 });

  return (
    <div>
      <h1>Thumbworx Live Tracking</h1>
      <MapWithNoSSR positions={data || []} />
    </div>
  );
}
