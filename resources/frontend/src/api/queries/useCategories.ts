import { useQuery } from "@tanstack/react-query";
import { apiClient } from "../../lib/api-client";
import type { QueryType } from "../utils/type";

export const useCategories = (): QueryType => {
  const { data, isLoading, error } = useQuery({
    queryKey: ["categories"],
    queryFn: async () => {
      const response = await apiClient.get("/categories");
      return response.data;
    },
    staleTime: Infinity,
    gcTime: Infinity,
    refetchOnWindowFocus: false,
    refetchOnReconnect: false,
    refetchOnMount: false,
    retry: 3,
  });

  return { data, isLoading, error };
};
