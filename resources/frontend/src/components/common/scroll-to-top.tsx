import { useState, useEffect } from "react";
import { ArrowUp } from "lucide-react";

export const ScrollToTop = () => {
  const [isVisible, setIsVisible] = useState(false);

  useEffect(() => {
    const toggleVisibility = () => {
      if (window.scrollY > 300) {
        setIsVisible(true);
      } else {
        setIsVisible(false);
      }
    };

    window.addEventListener("scroll", toggleVisibility);

    return () => window.removeEventListener("scroll", toggleVisibility);
  }, []);

  const scrollToTop = () => {
    window.scrollTo({
      top: 0,
      behavior: "smooth",
    });
  };

  if (!isVisible) {
    return null;
  }

  return (
    <button
      onClick={scrollToTop}
      className="fixed bottom-12 md:bottom-4 right-2 md:right-4 z-50 rounded-full bg-primary/70 hover:bg-primary/90 shadow-lg transition-transform duration-300 hover:scale-110 cursor-pointer p-2 flex items-center justify-center text-primary-foreground"
      aria-label="Scroll to top">
      <ArrowUp className="size-4 md:size-6" />
    </button>
  );
};
