import type { ProductDetailsType, ProductType } from "@/type";
import { cn } from "@/lib/utils";
import React, { useEffect } from "react";
import {
  findVariantByColorAndSize,
  getCurrentStock,
  getUniqueColors,
  getUniqueSizes,
  getVariant,
} from "@/helper";
import { Button } from "@/components/ui/button";
import { useCampaignAddToCart } from "@/controllers/campaignController";

interface Props {
  quantity: number;
  product: ProductDetailsType;
  selectedColor: string | null;
  selectedSize: string | null;
  setSelectedColor: React.Dispatch<React.SetStateAction<string | null>>;
  setSelectedSize: React.Dispatch<React.SetStateAction<string | null>>;
  setQuantity: (quantity: number) => void;
  setDisplayPrice: (price: string) => void;
  onVariantImageChange?: (image: string) => void;
}

export const LandingVariantCard = ({
  product,
  quantity,
  setQuantity,
  selectedColor,
  selectedSize,
  setSelectedColor,
  setSelectedSize,
  setDisplayPrice,
}: Props) => {
  const { isLoading, fnAddToCart } = useCampaignAddToCart(
    product as unknown as ProductType,
    quantity
  );

  const handleColorSelect = (color: string, e: React.MouseEvent): void => {
    e.stopPropagation();

    if (!color || color.trim() === "") return;

    setSelectedColor((prev: string | null) => (prev !== color ? color : prev));

    const variant = getVariant(color, selectedSize, product?.variants);

    if (selectedSize) {
      const variant = findVariantByColorAndSize(product, color, selectedSize);
      if (variant) {
        setDisplayPrice(variant?.variant_price_string);
      }
    } else {
      const colorVariant = product?.variants?.find(
        (v) => v?.color_code === color
      );
      if (colorVariant) {
        setDisplayPrice(colorVariant?.variant_price_string);
      }
    }

    fnAddToCart(variant as string | undefined);
  };

  const handleSizeSelect = (size: string, e: React.MouseEvent): void => {
    e.stopPropagation();
    if (!size || size.trim() === "") return;

    setSelectedSize((prev: string | null) => (prev !== size ? size : prev));

    if (selectedColor) {
      const variant = findVariantByColorAndSize(product, selectedColor, size);
      if (variant) {
        setDisplayPrice(variant?.variant_price_string);
      }
    } else {
      const sizeVariant = product?.variants?.find((v) => v.size_name === size);
      if (sizeVariant) {
        setDisplayPrice(sizeVariant?.variant_price_string);
      }
    }
    const variant = getVariant(selectedColor, size, product?.variants);

    fnAddToCart(variant as string | undefined);
  };

  useEffect(() => {
    if (product?.variants && product?.variants?.length > 0) {
      const firstVariant = product?.variants[0];
      setSelectedColor(firstVariant?.color_code);
      setSelectedSize(firstVariant?.size_name);
      setDisplayPrice(firstVariant?.variant_price_string);
    } else {
      setDisplayPrice(
        `${product?.currency_symbol}${product?.calculable_price}` ||
          product?.main_price ||
          "৳0"
      );
    }
  }, [product, setDisplayPrice, setSelectedColor, setSelectedSize]);

  useEffect(() => {
    const currentStock = getCurrentStock(product, selectedColor, selectedSize);
    if (quantity > currentStock?.stock && currentStock?.stock > 0) {
      setQuantity(currentStock?.stock);
    } else if (currentStock?.stock === 0) {
      setQuantity(0);
    }
  }, [selectedColor, selectedSize, product, quantity, setQuantity]);

  return (
    <>
      {product?.variants &&
        product?.variants?.length > 0 &&
        getUniqueColors(product).length > 0 && (
          <div className="flex items-center gap-2 md:mb-1 mb-2">
            <span className="text-sm font-medium">Colors:</span>
            <div className="flex items-center gap-2">
              {getUniqueColors(product)?.map((color) => {
                return (
                  <button
                    key={color}
                    disabled={isLoading}
                    onClick={(e) => handleColorSelect(color, e)}
                    className={cn(
                      "rounded-full cursor-pointer flex items-center justify-center transition-all duration-200 border-3",
                      selectedColor === color
                        ? "border-primary"
                        : "border-transparent"
                    )}
                    aria-label={`Select ${color} color`}>
                    <div
                      className="w-5 h-5 md:w-6 md:h-6 m-1 rounded-full flex items-center justify-center"
                      style={{
                        backgroundColor: color,
                      }}
                    />
                  </button>
                );
              })}
            </div>
          </div>
        )}

      {product?.variants &&
        product?.variants?.length > 0 &&
        getUniqueSizes(product)?.length > 0 && (
          <div className="flex items-center flex-wrap gap-2 mb-1 md:mb-2">
            <span className="text-sm font-medium">{"Sizes"}:</span>
            <div className="flex items-center flex-wrap gap-2">
              {getUniqueSizes(product)?.map((size) => (
                <Button
                  key={size}
                  variant={selectedSize === size ? "default" : "outline"}
                  disabled={isLoading}
                  onClick={(e) => handleSizeSelect(size, e)}
                  className={cn(
                    "flex items-center justify-center transition-all",
                    selectedSize === size
                      ? "bg-primary text-primary-foreground"
                      : "hover:border-primary/50"
                  )}
                  aria-label={`Select ${size} size`}>
                  <span className="text-sm">{size}</span>
                </Button>
              ))}
            </div>
          </div>
        )}

      <div className="flex items-center gap-2 mb-1 md:mb-2">
        {(() => {
          const currentStock = getCurrentStock(
            product,
            selectedColor,
            selectedSize
          );
          const isOutOfStock = currentStock?.stock === 0;

          return (
            <div className="flex items-center gap-2">
              <div
                className={cn(
                  "w-3 h-3 rounded-full",
                  isOutOfStock ? "bg-red-500" : "bg-green-500"
                )}
              />
              <span
                className={cn(
                  "text-xs md:text-sm font-medium",
                  isOutOfStock && "text-red-600",
                  !isOutOfStock && "text-green-600"
                )}>
                {isOutOfStock
                  ? "Out of stock"
                  : !isOutOfStock &&
                    `${currentStock?.stock} ${
                      currentStock?.unit
                    } ${"in stock"}`}
              </span>
            </div>
          );
        })()}
      </div>
    </>
  );
};
