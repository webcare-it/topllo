import type { HomePropsType } from "@/type";

import { ProductSection } from "@/components/common/product-section";

export const TodaysDealSection = ({ isLoading, products }: HomePropsType) => {
    return (
        <section
            className={`container mx-auto ${
                products?.length === 0 && !isLoading && "hidden"
            }`}
        >
            <ProductSection
                title="Today's Deal"
                products={products}
                isLoading={isLoading}
            />
        </section>
    );
};

export const NewArrivalsSection = ({ isLoading, products }: HomePropsType) => {
    return (
        <section
            className={`container mx-auto ${
                products?.length === 0 && !isLoading && "hidden"
            }`}
        >
            <ProductSection
                title="New Arrivals"
                products={products}
                isLoading={isLoading}
            />
        </section>
    );
};
