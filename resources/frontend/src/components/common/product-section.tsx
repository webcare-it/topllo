import {
  HomeSectionTitle,
  HomeSectionTitleSkeleton,
} from "@/components/common/section-title";
import type { ProductType } from "@/type";
import { CardLayout } from "@/components/common/card-layout";
import { ProductCard, ProductCardSkeleton } from "@/components/card/product";
import { useInitialLength, useIsMobile } from "@/hooks/useMobile";
import { Button } from "@/components/ui/button";
import { ArrowDown, ArrowUp } from "lucide-react";
import { useState } from "react";

interface Props {
  title: string;
  isLoading: boolean;
  products: ProductType[] | [];
}

export const ProductSection = ({ isLoading, products, title }: Props) => {
  const isMobile = useIsMobile();
  const initialLength = useInitialLength();

  const [showAll, setShowAll] = useState(false);

  const handleViewAll = () => {
    setShowAll(true);
  };

  const handleSeeLess = () => {
    setShowAll(false);
  };

  const displayedProducts = showAll
    ? products
    : products?.slice(0, initialLength);

  if (!products || products?.length === initialLength) return null;

  if (isLoading)
    return (
      <div className="w-full">
        <div className="my-6">
          <HomeSectionTitleSkeleton />
        </div>

        <CardLayout>
          {Array.from({ length: initialLength }).map((_, i) => (
            <ProductCardSkeleton key={i} />
          ))}
        </CardLayout>
      </div>
    );

  return (
    <HomeSectionTitle title={title}>
      <CardLayout>
        {displayedProducts?.map((product) => (
          <ProductCard key={product?.id} product={product} />
        ))}
      </CardLayout>
      <div className="flex justify-center items-center mt-4">
        {!showAll && products && products?.length > initialLength ? (
          <Button onClick={handleViewAll} size={isMobile ? "sm" : "default"}>
            View All
            <ArrowDown className="w-6 h-6 font-bold group-hover:translate-x-1 transition-transform" />
          </Button>
        ) : showAll && products && products?.length > initialLength ? (
          <Button onClick={handleSeeLess} size={isMobile ? "sm" : "default"}>
            See Less
            <ArrowUp className="w-6 h-6 font-bold group-hover:-translate-x-1 transition-transform" />
          </Button>
        ) : null}
      </div>
    </HomeSectionTitle>
  );
};
