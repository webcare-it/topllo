import type { HomePropsType } from "@/type";

import { ProductSection } from "@/components/common/product-section";

export const FeaturedProductsSection = ({
  isLoading,
  products,
}: HomePropsType) => {
  return (
    <section
      className={`container mx-auto ${
        products?.length === 0 && !isLoading && "hidden"
      }`}>
      <ProductSection
        title={"Featured Products"}
        products={products}
        isLoading={isLoading}
      />
    </section>
  );
};
