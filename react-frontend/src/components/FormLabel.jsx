/**
 * FormLabel Component
 * Reusable label component with consistent styling
 */
export function FormLabel({ children, htmlFor, className = "", ...props }) {
  return (
    <label
      htmlFor={htmlFor}
      className={`block text-label mb-2 ${className}`}
      {...props}
    >
      {children}
    </label>
  );
}
