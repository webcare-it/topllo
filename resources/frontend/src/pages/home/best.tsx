import type { HomePropsType } from "@/type";
import { useConfig } from "@/hooks/useConfig";
import { getConfig } from "@/helper";

import { ProductSection } from "@/components/common/product-section";

export const BestSellerSection = ({ isLoading, products }: HomePropsType) => {
  const config = useConfig();

  const isShow = getConfig(config, "best_selling")?.value as string;

  return isShow ? (
    <section
      className={`container mx-auto  ${
        products?.length === 0 && !isLoading && "hidden"
      }`}>
      <ProductSection
        title={"Best Sellers"}
        products={products}
        isLoading={isLoading}
      />
    </section>
  ) : null;
};
