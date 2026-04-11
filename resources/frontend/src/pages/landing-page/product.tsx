import { Title } from "./common";
import { useEffect, useMemo, useRef, useState } from "react";
import { cn } from "../../lib/utils";
import { LandingVariantCard } from "./variant";
import type { LandingPageType } from "./type";
import { getVariant } from "@/helper";
import type { ProductDetailsType, ProductType, StateSyncType } from "@/type";
import { useDispatch, useSelector } from "react-redux";
import type { RootStateType } from "@/redux/store";
import { useCampaignAddToCart } from "@/controllers/campaignController";
import { useGetCampaignCartQuery } from "@/api/queries/useGetCart";
import { setCartItemsCampaign } from "@/redux/slice/campaignSlice";
import { useGtmTracker, type PurchaseTrackerType } from "@/hooks/useGtmTracker";
import { useIntersectionObserver } from "@/hooks/useIntersectionObserver";
import { OptimizedImage } from "@/components/common/optimized-image";

interface Props {
    info: LandingPageType;
}
type StateType = string | null;

export const ProductSection = ({ info }: Props) => {
    const hasMounted = useRef(false);

    const campaign = useSelector(
        (state: RootStateType) => state.campaign?.items,
    );
    const { startCheckoutTracker } = useGtmTracker();
    const products = useMemo(
        () => info?.products?.data || [],
        [info?.products?.data],
    );
    const { ref, isIntersecting } = useIntersectionObserver({
        threshold: 0.1,
        rootMargin: "0px",
        triggerOnce: true,
    });

    useEffect(() => {
        if (
            isIntersecting &&
            products?.length > 0 &&
            !hasMounted.current &&
            campaign?.length > 0
        ) {
            hasMounted.current = true;

            const trackerData: PurchaseTrackerType = {
                transaction_id: Math.random().toString(36).substring(2, 15),
                value: products?.[0]?.calculable_price || 0,
                customer_type: "new",
                items: [
                    {
                        item_id: String(products?.[0]?.id || ""),
                        item_name: products?.[0]?.name || "",
                        item_price: products?.[0]?.calculable_price || 0,
                        item_quantity: 1,
                        item_category: products?.[0]?.category_name || "",
                        item_variant:
                            products?.[0]?.variants?.[0]?.size_name &&
                            products?.[0]?.variants?.[0]?.color_name
                                ? `${products?.[0]?.variants?.[0]?.size_name} - ${products?.[0]?.variants?.[0]?.color_name}`
                                : products?.[0]?.variants?.[0]?.size_name ||
                                  products?.[0]?.variants?.[0]?.color_name ||
                                  "",
                    },
                ],
            };

            startCheckoutTracker(trackerData);
        }
    }, [isIntersecting, products, startCheckoutTracker, campaign]);

    return (
        <section id="order-section" ref={ref}>
            {products?.length > 0 ? (
                <>
                    <Title>Choose Your Favorite Products</Title>

                    <div
                        className={`grid grid-cols-1 md:grid-cols-${products?.length > 2 ? 2 : 1} gap-4`}
                    >
                        {products?.map((product) => (
                            <SingleProduct key={product.id} product={product} />
                        ))}
                    </div>
                </>
            ) : null}
        </section>
    );
};

const SingleProduct = ({ product }: { product: ProductDetailsType }) => {
    const dispatch = useDispatch();
    const [quantity, setQuantity] = useState<number>(1);
    const [displayPrice, setDisplayPrice] = useState<string>("0");
    const [selectedSize, setSelectedSize] = useState<StateType>(null);
    const [selectedColor, setSelectedColor] = useState<StateType>(null);
    const campaign = useSelector(
        (state: RootStateType) => state.campaign?.items,
    );
    const { data, isLoading: isCartLoading } = useGetCampaignCartQuery();

    useEffect(() => {
        if (!isCartLoading) {
            if (data && data?.length > 0 && data?.[0]?.cart_items?.length > 0) {
                const cart = data?.[0]?.cart_items?.map((item) => {
                    const result: StateSyncType = {
                        id: item?.id,
                        productId: item?.product_id,
                        name: item?.product_name,
                        category_name: item?.category_name,
                        image: item?.product_thumbnail_image,
                        mainPrice: item?.price,
                        showPrice: `${item?.currency_symbol} ${item?.price}`,
                        variant: item?.variation,
                        quantity: item?.quantity,
                    };
                    return result;
                });

                dispatch(setCartItemsCampaign(cart as StateSyncType[]));
            } else {
                dispatch(setCartItemsCampaign([]));
            }
        }
    }, [data, isCartLoading, dispatch]);

    const isInCart = campaign?.find((item) => item.productId === product.id);

    const { isLoading, fnAddToCart } = useCampaignAddToCart(
        product as unknown as ProductType,
        quantity,
        getVariant(selectedColor, selectedSize, product?.variants),
    );

    return (
        <div
            onClick={() => fnAddToCart()}
            key={product.id}
            className={cn(
                "rounded-lg shadow-md p-1 md:p-3 cursor-pointer border hover:border-primary/50 transition-all duration-300  hover:bg-primary/5",
                isLoading && "opacity-75 cursor-not-allowed",
                isInCart && "border-primary hover:border-primary bg-primary/5",
            )}
        >
            <div className="flex items-start gap-2 md:gap-4">
                <div className="relative min-h-[140px] max-h-[200px] w-24 md:w-32 overflow-hidden rounded-lg">
                    <OptimizedImage
                        src={product?.thumbnail_image || ""}
                        alt={product?.name}
                        className="absolute w-full h-full object-cover"
                    />
                </div>

                <div className="flex-1">
                    <h2 className="text-sm md:text-base font-semibold line-clamp-1">
                        {product?.name}
                    </h2>
                    <div className="flex items-center gap-2">
                        <span className="text-sm md:text-base lg:text-lg font-bold text-foreground">
                            {displayPrice}
                        </span>
                    </div>

                    <LandingVariantCard
                        product={product}
                        quantity={quantity}
                        setQuantity={setQuantity}
                        selectedSize={selectedSize}
                        selectedColor={selectedColor}
                        setSelectedSize={setSelectedSize}
                        setSelectedColor={setSelectedColor}
                        setDisplayPrice={setDisplayPrice}
                    />
                </div>
            </div>
        </div>
    );
};
