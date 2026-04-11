import type { BrandType } from "@/type";
import { Link, useLocation } from "react-router-dom";
import { slugify } from "@/helper";
import { Skeleton } from "@/components/common/skeleton";
import { useGetTopBrands } from "@/api/queries/useAllBrands";

export const BrandFooter = () => {
  const location = useLocation();
  const { data, isLoading } = useGetTopBrands();
  const brands = data?.data as BrandType[];

  return (
    <div>
      <h4 className="text-white font-bold text-lg mb-4">Top Brands</h4>
      <ul className="space-y-2">
        {isLoading ? (
          Array.from({ length: 7 }).map((_, i) => (
            <Skeleton key={i} className="w-2/3 h-5 rounded" />
          ))
        ) : (
          <>
            {brands && brands?.length > 0 ? (
              brands?.map((item) => (
                <li key={item?.name}>
                  <Link
                    to={`/brands/${item?.id}/${slugify(item?.name)}`}
                    className={`hover:text-primary/70 hover:underline transition-colors text-sm line-clamp-1 ${
                      location.pathname ===
                      `/brands/${item?.id}/${slugify(item?.name)}`
                        ? "text-primary"
                        : ""
                    }`}>
                    {item?.name}
                  </Link>
                </li>
              ))
            ) : (
              <li key="no-brands">
                <span className="text-sm text-white">No brands found</span>
              </li>
            )}
          </>
        )}
      </ul>
    </div>
  );
};
