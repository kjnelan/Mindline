export function TabButton({ active, onClick, children }) {
  return (
    <button
      onClick={onClick}
      className={
        `px-4 py-2 font-semibold transition-colors ` +
        (active
          ? 'border-b-2 border-blue-600 text-blue-600'
          : 'text-gray-600 hover:text-gray-800')
      }
    >
      {children}
    </button>
  );
}
