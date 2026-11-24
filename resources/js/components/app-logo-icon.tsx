import { useId } from 'react';
import type { SVGAttributes } from 'react';

export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
    const id = useId();
    const top = `${id}-top`;
    const bottom = `${id}-bottom`;

    return (
        <svg
            {...props}
            viewBox="0 0 42 42"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path
                d="M7.98 25.62V17.64C7.98 8.4 15.12 1.68 23.94 1.68H36.54C40.32 1.68 42 4.2 42 8.4V10.08C42 14.7 38.64 17.22 34.02 17.22H23.94C19.74 17.22 17.64 19.74 17.64 23.94V25.62H7.98Z"
                fill={`url(#${top})`}
            />
            <path
                d="M7.14 27.3H17.64V23.94C17.64 19.32 20.16 17.22 24.78 17.22H33.6C37.8 17.22 39.48 20.16 39.48 24.36V27.3C39.48 36.12 32.76 41.16 23.52 41.16H5.88C2.52 41.16 0 38.64 0 34.86V33.6C0 29.4 2.94 27.3 7.14 27.3ZM21 16.8H30.66V24.36C30.66 27.44 28.98 28.98 25.62 28.98H17.64V21C17.64 18.2 18.76 16.8 21 16.8Z"
                fillRule="evenodd"
                clipRule="evenodd"
                fill={`url(#${bottom})`}
            />
            <defs>
                <linearGradient
                    id={top}
                    x1="7.98"
                    y1="25.62"
                    x2="30.5144"
                    y2="-6.4025"
                    gradientUnits="userSpaceOnUse"
                >
                    <stop stopColor="#30D0EB" />
                    <stop offset="0.56" stopColor="#1AA7FF" />
                    <stop offset="1" stopColor="#124EF0" />
                </linearGradient>
                <linearGradient
                    id={bottom}
                    x1="0"
                    y1="41.16"
                    x2="21.7722"
                    y2="5.87394"
                    gradientUnits="userSpaceOnUse"
                >
                    <stop stopColor="#45E6AE" />
                    <stop offset="0.55" stopColor="#0AC4D1" />
                    <stop offset="1" stopColor="#1375F5" />
                </linearGradient>
            </defs>
        </svg>
    );
}
