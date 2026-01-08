/**
 * FlatWP Logo Components
 * Dark branded versions of the FlatWP logo for the admin plugin interface
 */

interface LogoProps {
  className?: string;
}

/**
 * Dark Icon version for header and navigation
 * Optimized for 24x24 to 64x64 display
 */
export function LogoIcon({ className = "h-6 w-6" }: LogoProps) {
  return (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 250 250"
      className={className}
      aria-label="FlatWP Icon"
    >
      <defs>
        <style>{`
          .logo-icon-orange { fill: #ffa631; }
          .logo-icon-dark { fill: #2e3033; }
        `}</style>
      </defs>
      <path className="logo-icon-dark" d="M91.94,178.62c-4.28-4.41-6.35-12.18-6.35-23.74V12.38h-52.57v152.79c0,11.36,1.54,21.56,4.56,30.34,2.96,8.6,7.45,16.08,13.35,22.24,5.87,6.14,13.01,10.81,21.22,13.88,8.38,3.13,18.14,4.72,29.03,4.72h49.57v-51.09h-35.87c-11.1,0-18.6-2.17-22.92-6.64h0Z"/>
      <path className="logo-icon-dark" d="M164.79,135.29v99.93h52.2v-110.21c0-11.36-1.53-21.57-4.52-30.35-2.94-8.59-7.4-16.08-13.25-22.23-5.83-6.14-12.92-10.81-21.08-13.88-8.31-3.14-18.01-4.73-28.82-4.73h-49.68v51.09h35.87c11.1,0,18.6,2.17,22.93,6.64,4.27,4.42,6.35,12.18,6.35,23.74Z"/>
      <g>
        <path className="logo-icon-orange" d="M145.91,173.05h-34.79c-8.32,0-15.06-7.7-15.06-17.19l49.85,17.19Z"/>
        <g>
          <path className="logo-icon-orange" d="M116.63,173.05v-.03h-4.77,0c-6.73.01-8.06-8.88-7.97-11.93v-45.1s15.06,0,15.06,0h19.72c8.32,0,15.06,7.7,15.06,17.19v39.87h-37.11Z"/>
          <polygon className="logo-icon-orange" points="96.06 155.86 96.06 115.99 105.81 115.99 135.5 115.99 149.47 155.86 153.74 157.38 153.74 173.05 103.05 163.37 96.06 155.86"/>
        </g>
      </g>
    </svg>
  );
}

/**
 * Larger icon version for splash screens or prominent displays
 * Dark branded, suitable for 48x48 and larger
 */
export function LogoLarge({ className = "h-12 w-12" }: LogoProps) {
  return <LogoIcon className={className} />;
}

/**
 * Full wordmark logo with "flat" text
 * Dark gray with blue accent for headers and prominent branding areas
 */
export function LogoFullWordmark({ className = "h-8" }: LogoProps) {
  return (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 471 250"
      className={className}
      aria-label="FlatWP Full Logo"
    >
      <defs>
        <style>{`
          .logo-wordmark-dark { fill: #2e3033; }
          .logo-wordmark-blue { fill: #2563eb; }
        `}</style>
      </defs>
      <path className="logo-wordmark-dark" d="M209.91,179.26c-4.28-4.41-6.35-12.18-6.35-23.74V13.02h-52.57v152.79c0,11.36,1.54,21.56,4.56,30.34,2.96,8.6,7.45,16.08,13.35,22.24,5.87,6.14,13.01,10.81,21.22,13.88,8.38,3.13,18.14,4.72,29.03,4.72h49.57v-51.09h-35.87c-11.1,0-18.6-2.17-22.92-6.64h0Z"/>
      <path className="logo-wordmark-dark" d="M137.03,13.1l-20.1-.09c-15.37,0-38.11,4.44-51.84,12.92-7.01,4.33-13.21,9.35-18.41,14.91-5.19,5.55-9.54,11.81-12.95,18.61h0s-.02.02-.02.02c-5.38,10.63-8.11,23.77-8.11,39.06v138.45h54.75v-65.08h39.6v-55.41h-39.6v-11.39c0-11.17,2.73-20.06,8.13-26.4,5.68-6.68,13.72-10.06,23.88-10.06.21,0,15.66.03,24.67.09V13.1h0Z"/>
      <path className="logo-wordmark-dark" d="M282.76,135.92v99.93h52.2v-110.21c0-11.36-1.53-21.57-4.52-30.35-2.94-8.59-7.4-16.08-13.25-22.23-5.83-6.14-12.92-10.81-21.08-13.88-8.31-3.14-18.01-4.73-28.82-4.73h-49.68v51.09h35.87c11.1,0,18.6,2.17,22.93,6.64,4.27,4.42,6.35,12.18,6.35,23.74Z"/>
      <path className="logo-wordmark-dark" d="M389.94,228.14c11.58,6.29,24.07,9.48,37.14,9.48.52,0,1.16,0,1.92-.02v-56.98c-9.88-.79-17.11-3.96-21.51-9.44-4.97-6.2-7.4-16.52-7.4-31.55v-5.45h45.3v-56.64h-45.3V12.38h-51.38v134.68c0,23.09,4.48,41.76,13.33,55.5,6.95,10.69,16.34,19.3,27.89,25.58h0Z"/>
      <g>
        <path className="logo-wordmark-blue" d="M263.89,173.68h-34.79c-8.32,0-15.06-7.7-15.06-17.19l49.85,17.19Z"/>
        <g>
          <path className="logo-wordmark-blue" d="M234.61,173.68v-.03h-4.77,0c-6.73.01-8.06-8.88-7.97-11.93v-45.1s15.06,0,15.06,0h19.72c8.32,0,15.06,7.7,15.06,17.19v39.87h-37.11Z"/>
          <polygon className="logo-wordmark-blue" points="214.04 156.49 214.04 116.62 223.79 116.62 253.47 116.62 267.44 156.49 271.71 158.02 271.71 173.68 221.02 164 214.04 156.49"/>
        </g>
      </g>
    </svg>
  );
}

/**
 * Simple wordmark with icon and text
 * For compact headers
 */
export function LogoWordmark({ className = "h-8" }: LogoProps) {
  return (
    <div className={`flex items-center gap-2 ${className}`}>
      <LogoIcon className="h-6 w-6" />
      <span className="font-semibold text-lg text-gray-700">FlatWP</span>
    </div>
  );
}
