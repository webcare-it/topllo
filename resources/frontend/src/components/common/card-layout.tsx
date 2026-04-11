export const CardLayout = ({ children }: { children: React.ReactNode }) => {
  return (
    <section className="grid grid-cols-2 lg:grid-cols-5 xl:grid-cols-6 gap-1 md:gap-3 xl:gap-4 px-1 md:px-0">
      {children}
    </section>
  );
};
