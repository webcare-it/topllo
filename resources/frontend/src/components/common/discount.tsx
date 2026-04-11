import type { ProductDetailsType, ProductType } from "@/type";
import { Badge } from "../ui/badge";
import { hasDiscount } from "@/helper";

interface Props {
  type: "CARD" | "DETAILS" | "INFO";
  product: ProductType | ProductDetailsType;
}

export const Discount = ({ product, type }: Props) => {
  const discount = hasDiscount(product?.main_price, product?.stroked_price);
  const isDiscount = discount > 0;

  if (type === "INFO" && product?.has_discount) {
    return (
      <>
        {isDiscount && (
          <p className="text-sm text-green-600 font-medium">
            {"You save"} {discount}%
          </p>
        )}
      </>
    );
  }

  return (
    <>
      {isDiscount && product?.has_discount && (
        <Badge
          className={`${
            type === "DETAILS" && "text-sm font-semibold"
          } z-10 top-2 left-2 absolute text-white`}
          variant="destructive">
          {discount}%
        </Badge>
      )}
    </>
  );
};
