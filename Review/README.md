## Magento Module - Custom Product GraphQL & Product Reviews Video

### Custom review upload in magento admin

It can add/edit review video in the magento admin review page

### Custom GraphQL

##### Query

* Under **interface ProductInterface**

  **custom_attributes**

  ```
  // No filter, It will fetch all product attributes
  custom_attributes {
    label
    code
    value
  }

  // With filter, It will fetch only the filtered product attributes
  custom_attributes(filter: ["color", "shape", "handle_material", "bristle_material"]) {
    label
    code
    value
  }
  ```

  **rating_breakdown**

  ```
  // It will fetch the overall rating breakdown 
  rating_breakdown {
    items {
      name
      value
      rating_count
    }
  }
  ```

  **reviews_video**

  ```
  // No pagination, It will fetch all reviews that have a review video
  reviews_video {
    items {
      average_rating
      nickname
      summary
      text
      video
      ratings_breakdown {
          name
          value
      }
    }
  }
  
  // With pagination, It will controlled by pagination and fetch reviews that have a review video 
  reviews_video(pageSize: 3, currentPage: 2) {
    items {
      average_rating
      nickname
      summary
      text
      video
      ratings_breakdown {
          name
          value
      }
    }
  }
  ```

* Under **type ProductReview**

  ```
  // It will fetch the review video url
  reviews {
    items {
      video
    }
  }
  ```

* Under **type ProductReview**

  ```
  // It will fetch the review video url
  reviews {
    items {
      video
    }
  }
  ```


##### Mutation Query

* Under **input CreateProductReviewInput**

   ```
   // Custom video key for uploading reviews video. Make that the video is converted to base64 including the data URI
   mutation {
     createProductReview(
       input: {
         ...
         video: ""
         ...
       }
   ) {
      ...
   }
   ```


### Full Example Below

*Note that some of GrapQL query keys are default from magento*

##### Query Simple Product by route URL

```
{
  route(url: "running-shoes.html") {
    ... on SimpleProduct {
      sku
      name
      price {
        regularPrice {
          amount {
            value
            currency
          }
        }
      }
      description {
        html
      }
      short_description {
        html
      }
      media_gallery_entries {
        label
        position
        disabled
        file
      }
      media_gallery {
        url,
        label
      }
      custom_attributes(filter: ["color", "shape", "handle_material", "bristle_material"]) {
        label
        code
        value
      }
      review_count
      rating_summary
      rating_breakdown {
        items {
            name
            value
            rating_count
        }
     }
      reviews {
        items {
          average_rating
          nickname
          summary
          text
          video
          ratings_breakdown {
            name
            value
          }
        }
      }
      reviews_video(pageSize: 4, currentPage: 1) {
        items {
            average_rating
            nickname
            summary
            text
            video
            ratings_breakdown {
                name
                value
            }
        }
      }
      upsell_products {
        name
        sku
        url_rewrites {
            url
            parameters {
                name
                value
            }
        }
        short_description {
          html
        }
        review_count
        rating_summary
        thumbnail {
            url
            label
            position
            disabled
        }
        image {
            url
            label
            position
            disabled
        }
        price {
          regularPrice {
            amount {
              value
              currency
            }
          }
        }
      }
    }
  }
}
```


##### Mutation CreateReview with video

```
mutation {
  createProductReview(
    input: {
      sku: "Running Shoes",
      nickname: "Roni",
      summary: "Great looking sweatshirt",
      text: "This sweatshirt looks and feels great. The zipper sometimes sticks a bit.",
      video: "<base64 encoded with data URI>"
      ratings: [
        {
          id: "NA==",
          value_id: "Mjc="
        }
      ]
    }
) {
    review {
      product {
        ... // <------- You can fetch more data here, Like same on the product query above
        sku
        custom_attributes(filter: ["color", "shape", "handle_material", "bristle_material"]) {
          label
          code
          value
        }
        rating_breakdown {
          items {
              name
              value
              rating_count
          }
       }
       reviews_video(pageSize: 4, currentPage: 1) {
        items {
            average_rating
            nickname
            summary
            text
            video
            ratings_breakdown {
                name
                value
            }
        }
      }
      }
      nickname
      summary
      text
      average_rating
      video
      ratings_breakdown {
        name
        value
      }
    }
  }
}

```