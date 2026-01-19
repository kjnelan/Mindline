function BackgroundEffects() {
    return (
        <>
        <div className="absolute inset-0 bg-gradient-mental"></div>

        {/* Decorative flowing shapes */}
        <div className="absolute inset-0 overflow-hidden pointer-events-none">
          {/* Top right flowing shape */}
          <svg className="absolute -top-32 -right-32 w-[800px] h-[800px] opacity-30" viewBox="0 0 800 800" fill="none">
            <path d="M400,0 Q600,100 700,300 T800,600 L800,0 Z" fill="url(#gradient1)" />
            <defs>
              <linearGradient id="gradient1" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stopColor="#6B9AC4" stopOpacity="0.3" />
                <stop offset="100%" stopColor="#A8C5A5" stopOpacity="0.1" />
              </linearGradient>
            </defs>
          </svg>

          {/* Bottom left flowing shape */}
          <svg className="absolute -bottom-32 -left-32 w-[700px] h-[700px] opacity-25" viewBox="0 0 700 700" fill="none">
            <path d="M0,700 Q100,600 200,500 T400,300 L0,300 Z" fill="url(#gradient2)" />
            <defs>
              <linearGradient id="gradient2" x1="0%" y1="100%" x2="100%" y2="0%">
                <stop offset="0%" stopColor="#A8C5A5" stopOpacity="0.3" />
                <stop offset="100%" stopColor="#E8DCC4" stopOpacity="0.1" />
              </linearGradient>
            </defs>
          </svg>

          {/* Center decorative curve */}
          <svg className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[1000px] h-[600px] opacity-20" viewBox="0 0 1000 600" fill="none">
            <path d="M0,300 Q250,100 500,300 T1000,300" stroke="url(#gradient3)" strokeWidth="2" fill="none" opacity="0.5" />
            <path d="M0,320 Q250,120 500,320 T1000,320" stroke="url(#gradient3)" strokeWidth="1.5" fill="none" opacity="0.3" />
            <defs>
              <linearGradient id="gradient3" x1="0%" y1="0%" x2="100%" y2="0%">
                <stop offset="0%" stopColor="#6B9AC4" stopOpacity="0.6" />
                <stop offset="50%" stopColor="#A8C5A5" stopOpacity="0.4" />
                <stop offset="100%" stopColor="#E8DCC4" stopOpacity="0.6" />
              </linearGradient>
            </defs>
          </svg>
        </div>

        {/* Floating particles */}
        <div className="absolute inset-0 overflow-hidden">
        <div className="particle particle-1"></div>
        <div className="particle particle-2"></div>
        <div className="particle particle-3"></div>
        <div className="particle particle-4"></div>
        <div className="particle particle-5"></div>
        </div>
        </>
    );
}

export default BackgroundEffects;
