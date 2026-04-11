import { apiClient } from "@/lib/api-client";
import { useQuery } from "@tanstack/react-query";
import type { QueryType } from "../utils/type";

export const useGetAllBrands = () => {
  const { data, isLoading, error } = useQuery({
    queryKey: ["get_all_brands"],
    queryFn: async () => {
      const response = await apiClient.get(`/brands`);
      return response.data;
    },
  });

  return { data, isLoading, error };
};

export const useGetTopBrands = (): QueryType => {
  const { data, isLoading, error } = useQuery({
    queryKey: ["get_top_brands"],
    queryFn: async () => {
      await new Promise((resolve) => setTimeout(resolve, 6000));
      const response = await apiClient.get(`/brands/top`);
      return response.data;
    },
  });

  return { data, isLoading, error };
};
